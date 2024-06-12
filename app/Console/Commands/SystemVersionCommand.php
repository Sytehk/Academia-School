<?php

namespace App\Console\Commands;

use App\Services\SystemVersionService;
use Illuminate\Console\Command;

class SystemVersionCommand extends Command
{
    protected $signature = 'system:version';

    protected $description = 'Display the current system version';

    public function handle(SystemVersionService $versionService): int
    {
        $this->info('Current version: '.$versionService->current());

        if ($latest = $versionService->latest()) {
            $this->line('Latest available: '.$latest);
            $this->line($versionService->isUpgradeAvailable()
                ? 'Status: Update available'
                : 'Status: Up to date');
        }

        return self::SUCCESS;
    }
}
