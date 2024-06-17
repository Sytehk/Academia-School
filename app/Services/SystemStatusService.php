<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

/**
 * Aggregates the independent signals a dashboard "is this system healthy"
 * widget needs: application version vs. latest known, pending database
 * migrations, and the public/storage symlink. Each is checked separately —
 * a version bump does not imply migrations ran.
 */
class SystemStatusService
{
    public function __construct(
        private readonly SystemVersionService $versionService,
    ) {}

    public function currentVersion(): string
    {
        return $this->versionService->current();
    }

    public function latestVersion(): ?string
    {
        return $this->versionService->latest();
    }

    public function updateAvailable(): bool
    {
        return $this->versionService->isUpgradeAvailable();
    }

    public function storageLinked(): bool
    {
        $link = public_path('storage');

        return is_link($link) || File::exists($link);
    }

    /**
     * Mirrors Laravel's own `migrate:status` path resolution (module
     * migration paths registered via loadMigrationsFrom + the default
     * database/migrations path) so this never drifts from what `migrate`
     * itself would run.
     */
    public function hasPendingMigrations(): bool
    {
        try {
            $migrator = app('migrator');

            if (! $migrator->repositoryExists()) {
                return false;
            }

            $ran = $migrator->getRepository()->getRan();
            $paths = array_merge($migrator->paths(), [database_path('migrations')]);
            $files = $migrator->getMigrationFiles($paths);

            foreach (array_keys($files) as $migrationName) {
                if (! in_array($migrationName, $ran, true)) {
                    return true;
                }
            }

            return false;
        } catch (\Throwable) {
            return false;
        }
    }

    public function needsAttention(): bool
    {
        return $this->updateAvailable()
            || $this->hasPendingMigrations()
            || ! $this->storageLinked();
    }
}
