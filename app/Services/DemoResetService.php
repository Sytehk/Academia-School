<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Nwidart\Modules\Facades\Module;

class DemoResetService
{
    public function __construct(
        private readonly DemoModeService $demoMode,
        private readonly SystemSettingService $settings,
    ) {}

    public function shouldRunScheduledReset(): bool
    {
        if (! $this->demoMode->autoResetEnabled()) {
            return false;
        }

        $lastReset = $this->settings->get('demo_last_reset_at');
        $intervalHours = $this->demoMode->resetIntervalHours();

        if (! $lastReset) {
            return true;
        }

        return now()->diffInHours($lastReset) >= $intervalHours;
    }

    public function reset(): void
    {
        if (! $this->demoMode->enabled()) {
            throw new \RuntimeException('Demo reset is only available when demo mode is enabled.');
        }

        Log::info('[Demo Reset] Starting selective demo data reset...');

        $protected = config('system.demo_reset.protected_tables', []);
        $resettable = config('system.demo_reset.resettable_tables', []);

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        if (! empty($resettable)) {
            foreach ($resettable as $table) {
                if (Schema::hasTable($table) && ! in_array($table, $protected, true)) {
                    DB::table($table)->truncate();
                    Log::info("[Demo Reset] Truncated table: {$table}");
                }
            }
        } else {
            $this->resetViaSeeders();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->settings->set('demo_last_reset_at', now()->toDateTimeString(), 'string', 'demo');

        Log::info('[Demo Reset] Demo data reset completed.');
    }

    private function resetViaSeeders(): void
    {
        foreach (config('system.demo_reset.seeders', []) as $seeder) {
            Artisan::call('db:seed', ['--class' => $seeder, '--force' => true]);
        }

        if (config('system.demo_reset.module_seed', true)) {
            foreach (Module::allEnabled() as $module) {
                try {
                    Artisan::call('module:seed', ['module' => $module->getName()]);
                } catch (\Throwable $e) {
                    Log::warning("[Demo Reset] Module seed skipped: {$module->getName()} — ".$e->getMessage());
                }
            }
        }
    }
}
