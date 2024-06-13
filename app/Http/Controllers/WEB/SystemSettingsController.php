<?php

namespace App\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Services\DemoModeService;
use App\Services\SystemSettingService;
use App\Services\SystemStatusService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use RealRashid\SweetAlert\Facades\Alert;

class SystemSettingsController extends Controller
{
    public function index(SystemSettingService $settings, DemoModeService $demoMode, SystemStatusService $status)
    {
        abort_unless(can_access_system('system.settings.manage'), 403);

        return view('system.settings.index', [
            'settings' => array_merge(
                $settings->allByGroup('general'),
                $settings->allByGroup('demo'),
            ),
            'demoMode' => $demoMode->enabled(),
            'migrationsPending' => $status->hasPendingMigrations(),
        ]);
    }

    public function migrate(SystemStatusService $status, DemoModeService $demoMode)
    {
        abort_unless(can_access_system('system.settings.manage'), 403);
        $demoMode->check('system_settings');

        if (! $status->hasPendingMigrations()) {
            Alert::toast('Database is already up to date.', 'info');

            return redirect()->route('system.settings.index');
        }

        try {
            Artisan::call('migrate', ['--force' => true]);
            Artisan::call('optimize:clear');
            Alert::toast('Database migrations completed.', 'success');
        } catch (\Throwable $e) {
            Log::error('[System Settings] Migration failed: '.$e->getMessage());
            Alert::toast('Migration failed. Check the application log.', 'error');
        }

        return redirect()->route('system.settings.index');
    }

    public function update(Request $request, SystemSettingService $settings, DemoModeService $demoMode)
    {
        abort_unless(can_access_system('system.settings.manage'), 403);
        $demoMode->check('system_settings');

        $validated = $request->validate([
            'update_server_url' => 'nullable|url|max:500',
            'update_check_enabled' => 'nullable|boolean',
            'demo_auto_reset' => 'nullable|boolean',
            'demo_reset_interval' => 'nullable|integer|min:1|max:168',
        ]);

        if (array_key_exists('update_server_url', $validated)) {
            $settings->set('update_server_url', $validated['update_server_url'], 'string', 'general');
        }

        $settings->set(
            'update_check_enabled',
            $request->boolean('update_check_enabled'),
            'boolean',
            'general'
        );

        $settings->set(
            'demo_auto_reset',
            $request->boolean('demo_auto_reset'),
            'boolean',
            'demo'
        );

        if (! empty($validated['demo_reset_interval'])) {
            $settings->set('demo_reset_interval', $validated['demo_reset_interval'], 'integer', 'demo');
        }

        Alert::toast('System settings saved.', 'success');

        return redirect()->route('system.settings.index');
    }
}
