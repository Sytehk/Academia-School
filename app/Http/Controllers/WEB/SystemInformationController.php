<?php

namespace App\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Services\DemoModeService;
use App\Services\SystemSettingService;
use App\Services\SystemVersionService;

class SystemInformationController extends Controller
{
    public function index(
        SystemVersionService $versionService,
        DemoModeService $demoMode,
        SystemSettingService $settings,
    ) {
        abort_unless(can_access_system('system.update.view'), 403);

        $remote = $versionService->checkRemote();

        return view('system.information', [
            'currentVersion' => $versionService->current(),
            'latestVersion' => $versionService->latest(),
            'updateAvailable' => $versionService->isUpgradeAvailable(),
            'demoMode' => $demoMode->enabled(),
            'phpVersion' => PHP_VERSION,
            'laravelVersion' => app()->version(),
            'environment' => app()->environment(),
            'maintenanceMode' => app()->isDownForMaintenance(),
            'settings' => $settings->allByGroup('general'),
            'remoteMetadata' => $remote,
        ]);
    }
}
