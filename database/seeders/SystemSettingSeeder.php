<?php

namespace Database\Seeders;

use App\Services\SystemSettingService;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = app(SystemSettingService::class);

        $defaultVersion = defined('SOFTWARE_VERSION') ? SOFTWARE_VERSION : config('system.version', '1.0.0');

        $settings->set('app_version', $defaultVersion, 'string', 'version');
        $settings->set('latest_version', null, 'string', 'version');
        $settings->set('update_server_url', config('system.updates.remote_check_url'), 'string', 'general');
        $settings->set('update_check_enabled', config('system.updates.remote_check_enabled', false), 'boolean', 'general');
        $settings->set('demo_mode', config('system.demo.enabled', false), 'boolean', 'demo');
        $settings->set('demo_auto_reset', config('system.demo.auto_reset', false), 'boolean', 'demo');
        $settings->set('demo_reset_interval', config('system.demo.reset_interval', 24), 'integer', 'demo');
    }
}
