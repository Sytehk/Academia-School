<?php

namespace App\Services;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Authentication\Models\SAASSetting;

/**
 * Full-database SQL dump, storable locally or on an S3-compatible remote
 * (Cloudflare R2 or AWS S3 — both speak the s3 driver). Credentials and the
 * chosen destination are read from s_a_a_s_settings so they can be managed
 * from the SaaS panel rather than only via .env.
 */
class DatabaseBackupService
{
    private const REMOTE_PREFIX = 'database-backups';

    /**
     * @return array{disk: string, path: string, size: int}
     */
    public function run(): array
    {
        $sql = $this->dump();
        $filename = 'DB-BACKUP-'.now()->format('Y-m-d_His').'-'.Str::random(8).'.sql';

        $settings = $this->settings();
        $useRemote = ($settings['backup_destination'] ?? 'local') === 's3';

        if ($useRemote && $this->remoteConfigured($settings)) {
            $disk = $this->remoteDisk($settings);
            $path = self::REMOTE_PREFIX.'/'.$filename;

            if (! $disk->put($path, $sql)) {
                throw new \RuntimeException('Could not write the backup to the configured remote destination.');
            }

            $this->pruneRemote($disk, (int) ($settings['backup_retention_days'] ?? 30));

            return ['disk' => 's3', 'path' => $path, 'size' => strlen($sql)];
        }

        $directory = storage_path('app/backups');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        $path = $directory.DIRECTORY_SEPARATOR.$filename;
        file_put_contents($path, $sql);

        $this->pruneLocal($directory, (int) ($settings['backup_retention_days'] ?? 30));

        return ['disk' => 'local', 'path' => $path, 'size' => strlen($sql)];
    }

    public function dump(): string
    {
        @ini_set('max_execution_time', 0);
        @set_time_limit(0);

        $database = 'Tables_in_'.DB::getDatabaseName();
        $tables = [];
        foreach (DB::select('SHOW TABLES') as $table) {
            $tables[] = $table->$database;
        }

        // Header lines make the dump actually restorable:
        //  - FOREIGN_KEY_CHECKS=0: without it the per-table DROP/CREATE below fails
        //    the moment two tables reference each other (this schema has many FKs).
        //  - NAMES utf8mb4: preserve multi-byte data (e.g. Bengali) instead of
        //    corrupting it on restore.
        //  - NO_AUTO_VALUE_ON_ZERO: keep literal 0s in auto-increment columns.
        $return = "SET FOREIGN_KEY_CHECKS=0;\n";
        $return .= "SET NAMES utf8mb4;\n";
        $return .= "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n\n";

        foreach ($tables as $table) {
            $return .= "DROP TABLE IF EXISTS `$table`;\n";
            $create = DB::select("SHOW CREATE TABLE `$table`");
            $return .= "\n".$create[0]->{'Create Table'}.";\n\n";

            // Stream rows with a cursor instead of SELECT *-ing the whole table
            // into memory; a single large table no longer has to be fully
            // hydrated on top of the dump string being built.
            foreach (DB::cursor("SELECT * FROM `$table`") as $row) {
                $values = [];
                foreach ((array) $row as $val) {
                    $values[] = $this->quoteValue($val);
                }
                $return .= "INSERT INTO `$table` VALUES(".implode(',', $values).");\n";
            }

            $return .= "\n\n";
        }

        $return .= "\nSET FOREIGN_KEY_CHECKS=1;\n";

        return $return;
    }

    /**
     * Render a single column value as safe SQL. Critically, NULL must be emitted
     * as the keyword NULL — the previous addslashes($val) turned every NULL into
     * an empty string, silently corrupting nullable columns on restore.
     */
    private function quoteValue(mixed $val): string
    {
        if ($val === null) {
            return 'NULL';
        }

        if (is_int($val) || is_float($val)) {
            return (string) $val;
        }

        return "'".addslashes((string) $val)."'";
    }

    public function isRemoteConfigured(): bool
    {
        return $this->remoteConfigured($this->settings());
    }

    /**
     * @return array{name: string, size: int, last_modified: int}[]
     */
    public function listRemote(): array
    {
        $settings = $this->settings();

        if (! $this->remoteConfigured($settings)) {
            return [];
        }

        $disk = $this->remoteDisk($settings);
        $files = $disk->files(self::REMOTE_PREFIX);

        return collect($files)
            ->map(fn (string $path) => [
                'name' => basename($path),
                'size' => $disk->size($path),
                'last_modified' => $disk->lastModified($path),
            ])
            ->sortByDesc('last_modified')
            ->values()
            ->all();
    }

    private function settings(): array
    {
        return SAASSetting::query()->pluck('value', 'name')->all();
    }

    private function remoteConfigured(array $settings): bool
    {
        return ! blank($settings['backup_aws_key'] ?? null)
            && ! blank($settings['backup_aws_secret'] ?? null)
            && ! blank($settings['backup_aws_bucket'] ?? null);
    }

    private function remoteDisk(array $settings): Filesystem
    {
        return Storage::build([
            'driver' => 's3',
            'key' => $settings['backup_aws_key'] ?? '',
            'secret' => $settings['backup_aws_secret'] ?? '',
            'region' => $settings['backup_aws_region'] ?? 'auto',
            'bucket' => $settings['backup_aws_bucket'] ?? '',
            'endpoint' => $settings['backup_aws_endpoint'] ?? null,
            'use_path_style_endpoint' => ($settings['backup_aws_use_path_style'] ?? 'no') === 'yes',
        ]);
    }

    private function pruneRemote(Filesystem $disk, int $retentionDays): void
    {
        if ($retentionDays <= 0) {
            return;
        }

        $cutoff = now()->subDays($retentionDays)->getTimestamp();

        foreach ($disk->files(self::REMOTE_PREFIX) as $path) {
            if ($disk->lastModified($path) < $cutoff) {
                $disk->delete($path);
            }
        }
    }

    private function pruneLocal(string $directory, int $retentionDays): void
    {
        if ($retentionDays <= 0) {
            return;
        }

        $cutoff = now()->subDays($retentionDays)->getTimestamp();

        foreach (glob($directory.DIRECTORY_SEPARATOR.'DB-BACKUP-*.sql') ?: [] as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
            }
        }
    }
}
