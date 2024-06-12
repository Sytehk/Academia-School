<?php

namespace App\Console\Commands;

use App\Services\DemoResetService;
use Illuminate\Console\Command;

class DemoResetCommand extends Command
{
    protected $signature = 'demo:reset {--force : Force reset even if interval not elapsed}';

    protected $description = 'Reset demo transactional data while preserving system configuration';

    public function handle(DemoResetService $demoReset): int
    {
        if (! $this->option('force') && ! $demoReset->shouldRunScheduledReset()) {
            $this->info('Demo reset not due yet.');

            return self::SUCCESS;
        }

        try {
            $demoReset->reset();
            $this->info('Demo data reset completed.');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
