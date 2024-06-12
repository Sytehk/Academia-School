<?php

namespace App\Console\Commands;

use App\Services\SystemUpdate\UpdateBackupManager;
use App\Services\SystemUpdate\UpdateManager;
use Illuminate\Console\Command;

class SystemUpdateCommand extends Command
{
    protected $signature = 'system:update {--file= : Path to update ZIP package}';

    protected $description = 'Install a system update from a ZIP package';

    public function handle(UpdateManager $updateManager, UpdateBackupManager $backupManager): int
    {
        $file = $this->option('file');

        if (! $file || ! file_exists($file)) {
            $this->error('Please provide a valid --file path to an update ZIP package.');

            return self::FAILURE;
        }

        try {
            $this->info('Validating update package...');
            $validation = $updateManager->validatePackage($file);

            if (! $validation['ready']) {
                $this->error('Package validation failed.');

                return self::FAILURE;
            }

            $this->info('Installing update to version '.$validation['metadata']['version'].'...');
            $update = $updateManager->install($file, 'cli');

            $this->info('Update completed: '.$update->from_version.' → '.$update->to_version);
            $this->line($updateManager->logger()->formatted());

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Update failed: '.$e->getMessage());
            $this->line($updateManager->logger()->formatted());

            return self::FAILURE;
        }
    }
}
