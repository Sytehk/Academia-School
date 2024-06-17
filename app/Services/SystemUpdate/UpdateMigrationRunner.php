<?php

namespace App\Services\SystemUpdate;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class UpdateMigrationRunner
{
    public function runFromPackage(string $extractPath, UpdateLogger $logger): void
    {
        $migrationsPath = $extractPath.DIRECTORY_SEPARATOR.'migrations';

        if (! is_dir($migrationsPath)) {
            $logger->info('No package migrations directory found — skipping.');

            return;
        }

        $targetPath = database_path('migrations/_system_update_'.now()->format('YmdHis'));
        File::ensureDirectoryExists($targetPath);

        foreach (File::files($migrationsPath) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            File::copy($file->getPathname(), $targetPath.DIRECTORY_SEPARATOR.$file->getFilename());
            $logger->info('Copied migration: '.$file->getFilename());
        }

        Artisan::call('migrate', ['--force' => true]);
        $output = trim(Artisan::output());

        if ($output) {
            $logger->info('Migration output: '.$output);
        }

        $logger->info('Migrations completed.');
    }
}
