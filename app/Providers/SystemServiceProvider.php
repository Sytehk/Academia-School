<?php

namespace App\Providers;

use App\Services\DemoModeService;
use App\Services\ModuleManagerService;
use App\Services\SystemSettingService;
use App\Services\SystemUpdate\UpdateManager;
use App\Services\SystemVersionService;
use Illuminate\Support\ServiceProvider;

class SystemServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(config_path('system.php'), 'system');

        $this->app->singleton(DemoModeService::class);
        $this->app->singleton(SystemSettingService::class);
        $this->app->singleton(SystemVersionService::class);
        $this->app->singleton(ModuleManagerService::class);
        $this->app->singleton(UpdateManager::class);
    }

    public function boot(): void
    {
        $this->app->macro('isDemoMode', function () {
            return is_demo_mode();
        });
    }
}
