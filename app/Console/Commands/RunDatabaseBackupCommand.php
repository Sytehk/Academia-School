<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;
use Modules\Authentication\Models\SAASSetting;

class RunDatabaseBackupCommand extends Command
{
    protected $signature = 'backup:run {--force : Run even if automatic backups are disabled in settings}';

    protected $description = 'Dump the database and store it locally or on the configured S3/R2 destination';

    public function handle(DatabaseBackupService $backups): int
    {
        $enabled = SAASSetting::query()->where('name', 'backup_auto_enabled')->value('value') === 'yes';

        if (! $this->option('force') && ! $enabled) {
            $this->info('Automatic backups are disabled in settings.');

            return self::SUCCESS;
        }

        try {
            $result = $backups->run();
            $this->info("Backup stored on [{$result['disk']}]: {$result['path']} ({$result['size']} bytes).");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
