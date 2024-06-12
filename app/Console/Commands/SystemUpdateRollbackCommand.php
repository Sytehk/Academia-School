<?php

namespace App\Console\Commands;

use App\Models\SystemUpdate;
use App\Services\SystemUpdate\UpdateManager;
use Illuminate\Console\Command;

class SystemUpdateRollbackCommand extends Command
{
    protected $signature = 'system:update:rollback {update_id : The system update record ID}';

    protected $description = 'Rollback a completed system update using its backup';

    public function handle(UpdateManager $updateManager): int
    {
        $update = SystemUpdate::query()->find($this->argument('update_id'));

        if (! $update) {
            $this->error('Update record not found.');

            return self::FAILURE;
        }

        try {
            $updateManager->rollback($update);
            $this->info('Rollback completed.');
            $this->line($updateManager->logger()->formatted());

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Rollback failed: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
