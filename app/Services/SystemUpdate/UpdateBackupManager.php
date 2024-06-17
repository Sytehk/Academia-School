<?php

namespace App\Services\SystemUpdate;

use App\Exceptions\SystemUpdateException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ZipArchive;

class UpdateBackupManager
{
    public function create(string $version, UpdateLogger $logger): string
    {
        $timestamp = now()->format('Y-m-d_His');
        $backupDir = storage_path('app/'.config('system.updates.backup_path')."/{$timestamp}_v{$version}");

        File::ensureDirectoryExists($backupDir);

        $this->backupDatabase($backupDir, $logger);
        $this->backupCriticalFiles($backupDir, $logger);

        $logger->info("Backup created at: {$backupDir}");

        return $backupDir;
    }

    public function restore(string $backupPath, UpdateLogger $logger): void
    {
        if (! is_dir($backupPath)) {
            throw new SystemUpdateException('Backup directory not found for rollback.', 'rollback');
        }

        $dbDump = $backupPath.'/database.sql';
        if (file_exists($dbDump)) {
            $this->restoreDatabase($dbDump, $logger);
        }

        $filesBackup = $backupPath.'/files.zip';
        if (file_exists($filesBackup)) {
            $this->restoreFiles($filesBackup, $logger);
        }

        $logger->info('Rollback completed from backup.');
    }

    private function backupDatabase(string $backupDir, UpdateLogger $logger): void
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        if (($config['driver'] ?? '') !== 'mysql') {
            $logger->warning('Database backup skipped: only MySQL is supported in this release.');

            return;
        }

        $dumpPath = $backupDir.'/database.sql';
        $host = escapeshellarg($config['host'] ?? '127.0.0.1');
        $port = escapeshellarg((string) ($config['port'] ?? '3306'));
        $database = escapeshellarg($config['database'] ?? '');
        $username = escapeshellarg($config['username'] ?? '');
        $password = $config['password'] ?? '';

        $passwordArg = $password !== '' ? '-p'.escapeshellarg($password) : '';

        $command = sprintf(
            'mysqldump --host=%s --port=%s --user=%s %s %s > %s 2>&1',
            $host,
            $port,
            $username,
            $passwordArg,
            $database,
            escapeshellarg($dumpPath)
        );

        exec($command, $output, $exitCode);

        if ($exitCode !== 0 || ! file_exists($dumpPath) || filesize($dumpPath) === 0) {
            $logger->warning('mysqldump unavailable — falling back to Laravel schema export marker.');
            file_put_contents($backupDir.'/database.fallback.txt', 'Manual database backup recommended.');
        } else {
            $logger->info('Database backup completed.');
        }
    }

    private function backupCriticalFiles(string $backupDir, UpdateLogger $logger): void
    {
        $paths = ['app', 'config', 'routes', 'Modules', 'resources/views', 'database/migrations'];
        $zipPath = $backupDir.'/files.zip';

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new SystemUpdateException('Failed to create files backup archive.', 'backup');
        }

        $base = base_path();
        foreach ($paths as $relative) {
            $full = $base.DIRECTORY_SEPARATOR.$relative;
            if (! is_dir($full)) {
                continue;
            }

            foreach (File::allFiles($full) as $file) {
                $entry = $relative.'/'.substr($file->getPathname(), strlen($full) + 1);
                $zip->addFile($file->getPathname(), str_replace('\\', '/', $entry));
            }
        }

        $zip->close();
        $logger->info('Application files backup completed.');
    }

    private function restoreDatabase(string $dumpPath, UpdateLogger $logger): void
    {
        $sql = file_get_contents($dumpPath);
        DB::unprepared('SET FOREIGN_KEY_CHECKS=0;');
        DB::unprepared($sql);
        DB::unprepared('SET FOREIGN_KEY_CHECKS=1;');
        $logger->info('Database restored from backup.');
    }

    private function restoreFiles(string $zipPath, UpdateLogger $logger): void
    {
        $zip = new ZipArchive;
        if ($zip->open($zipPath) !== true) {
            throw new SystemUpdateException('Failed to open files backup for restore.', 'rollback');
        }

        $zip->extractTo(base_path());
        $zip->close();
        $logger->info('Application files restored from backup.');
    }

    public function makeTempPackagePath(string $originalName): string
    {
        $dir = storage_path('app/'.config('system.updates.packages_path'));
        File::ensureDirectoryExists($dir);

        return $dir.'/'.Str::uuid().'_'.basename($originalName);
    }
}
