<?php

namespace App\Services\SystemUpdate;

use App\Exceptions\SystemUpdateException;
use App\Models\SystemUpdate;
use App\Services\DemoModeService;
use App\Services\SystemVersionService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class UpdateManager
{
    private ?SystemUpdate $record = null;

    private UpdateLogger $logger;

    public function __construct(
        private readonly DemoModeService $demoMode,
        private readonly SystemVersionService $versionService,
        private readonly UpdatePackageValidator $packageValidator,
        private readonly UpdateExtractor $extractor,
        private readonly UpdateFileManager $fileManager,
        private readonly UpdateMigrationRunner $migrationRunner,
        private readonly UpdateCommandRunner $commandRunner,
        private readonly UpdateBackupManager $backupManager,
        private readonly UpdateVersionManager $versionManager,
        private readonly UpdateLockManager $lockManager,
        private readonly UpdateHookDispatcher $hooks,
    ) {
        $this->logger = new UpdateLogger;
    }

    public function validatePackage(string $zipPath): array
    {
        $this->demoMode->check('system_upgrade');

        return $this->packageValidator->validate($zipPath);
    }

    public function install(string $zipPath, string $type = 'manual'): SystemUpdate
    {
        $this->demoMode->check('system_upgrade');

        if (! config('system.updates.enabled', true)) {
            throw new SystemUpdateException('System updates are disabled.', 'config');
        }

        $validation = $this->packageValidator->validate($zipPath);

        if (! $validation['ready']) {
            throw new SystemUpdateException('Update package failed validation.', 'validation', [
                'checks' => $validation['checks'],
            ]);
        }

        $metadata = $validation['metadata'];
        $fromVersion = $this->versionService->current();
        $toVersion = $metadata['version'];

        $this->record = SystemUpdate::query()->create([
            'from_version' => $fromVersion,
            'to_version' => $toVersion,
            'type' => $type,
            'status' => SystemUpdate::STATUS_PENDING,
            'package_name' => basename($zipPath),
            'package_size' => filesize($zipPath) ?: null,
            'started_at' => now(),
            'executed_by' => Auth::id(),
            'metadata' => $metadata,
        ]);

        $this->logger->attach($this->record);
        $this->logger->info('Update started');

        $extractPath = null;
        $backupPath = null;
        $maintenanceEnabled = false;

        try {
            $this->lockManager->acquire();
            $this->updateStatus(SystemUpdate::STATUS_VALIDATING);
            $this->logger->info('Package validated');

            $this->hooks->beforeUpdate($metadata);

            if (config('system.updates.maintenance_mode', true)) {
                Artisan::call('down', ['--retry' => 60, '--secret' => 'system-update-'.now()->timestamp]);
                $maintenanceEnabled = true;
                $this->logger->info('Maintenance mode enabled');
            }

            $this->updateStatus(SystemUpdate::STATUS_BACKUP);
            if (config('system.updates.backup_before_update', true)) {
                $backupPath = $this->backupManager->create($toVersion, $this->logger);
                $this->record->update(['backup_path' => $backupPath]);
                $this->hooks->afterBackup($metadata, $backupPath);
            }

            $this->updateStatus(SystemUpdate::STATUS_INSTALLING);
            $extractPath = storage_path('app/'.config('system.updates.temp_path').'/'.uniqid('update_', true));
            $this->extractor->extract($zipPath, $extractPath);
            $this->logger->info('Files extracted');

            $this->hooks->beforeFilesUpdate($metadata, $extractPath);
            $this->fileManager->apply($extractPath, $this->logger);
            $this->hooks->afterFilesUpdate($metadata);
            $this->logger->info('Files updated');

            $this->updateStatus(SystemUpdate::STATUS_MIGRATING);
            $this->hooks->beforeMigration($metadata);

            if ($metadata['run_migrations'] ?? true) {
                $this->migrationRunner->runFromPackage($extractPath, $this->logger);
            }

            $this->hooks->afterMigration($metadata);

            $this->commandRunner->run($metadata, $this->logger);

            if ($metadata['clear_cache'] ?? true) {
                Artisan::call('optimize:clear');
                $this->logger->info('Cache cleared');
            }

            $this->versionService->setCurrent($toVersion);
            $this->logger->info("Version updated to {$toVersion}");

            $this->record->update([
                'status' => SystemUpdate::STATUS_COMPLETED,
                'completed_at' => now(),
            ]);

            $this->hooks->afterUpdate($metadata);
            $this->logger->info('Update completed');

            return $this->record->fresh(['logs']);
        } catch (\Throwable $e) {
            $this->logger->error('Update failed: '.$e->getMessage());
            $this->record?->update([
                'status' => SystemUpdate::STATUS_FAILED,
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            $this->hooks->onUpdateFailed($metadata ?? [], $e);

            if ($backupPath && config('system.updates.backup_before_update', true)) {
                try {
                    $this->backupManager->restore($backupPath, $this->logger);
                    $this->record?->update(['status' => SystemUpdate::STATUS_ROLLED_BACK]);
                } catch (\Throwable $rollbackError) {
                    $this->logger->error('Rollback failed: '.$rollbackError->getMessage());
                }
            }

            throw $e instanceof SystemUpdateException
                ? $e
                : new SystemUpdateException($e->getMessage(), 'install', [], 0, $e);
        } finally {
            if ($maintenanceEnabled) {
                try {
                    Artisan::call('up');
                    $this->logger->info('Maintenance mode disabled');
                } catch (\Throwable) {
                    // Recovery note left in logs
                    $this->logger->warning('Failed to disable maintenance mode — run: php artisan up');
                }
            }

            $this->lockManager->release();

            if ($extractPath && is_dir($extractPath)) {
                File::deleteDirectory($extractPath);
            }
        }
    }

    public function rollback(SystemUpdate $update): void
    {
        $this->demoMode->check('system_upgrade');

        if (! $update->backup_path) {
            throw new SystemUpdateException('No backup available for this update.', 'rollback');
        }

        $this->logger->attach($update);
        $this->lockManager->acquire();

        try {
            $this->backupManager->restore($update->backup_path, $this->logger);

            if ($update->from_version) {
                $this->versionService->setCurrent($update->from_version);
            }

            $update->update([
                'status' => SystemUpdate::STATUS_ROLLED_BACK,
                'completed_at' => now(),
            ]);
        } finally {
            $this->lockManager->release();
        }
    }

    public function logger(): UpdateLogger
    {
        return $this->logger;
    }

    public function isLocked(): bool
    {
        return $this->lockManager->isLocked();
    }

    private function updateStatus(string $status): void
    {
        $this->record?->update(['status' => $status]);
    }
}
