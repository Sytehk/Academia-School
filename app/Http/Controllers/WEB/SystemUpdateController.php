<?php

namespace App\Http\Controllers\WEB;

use App\Exceptions\SystemUpdateException;
use App\Http\Controllers\Controller;
use App\Models\SystemUpdate;
use App\Services\DemoModeService;
use App\Services\SystemUpdate\UpdateBackupManager;
use App\Services\SystemUpdate\UpdateManager;
use App\Services\SystemVersionService;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class SystemUpdateController extends Controller
{
    public function index(
        SystemVersionService $versionService,
        DemoModeService $demoMode,
        UpdateManager $updateManager,
    ) {
        abort_unless(can_access_system('system.update.view'), 403);

        $remote = $versionService->checkRemote();

        return view('system.update.index', [
            'currentVersion' => $versionService->current(),
            'latestVersion' => $versionService->latest(),
            'updateAvailable' => $versionService->isUpgradeAvailable(),
            'demoMode' => $demoMode->enabled(),
            'demoRestricted' => $demoMode->isRestricted('system_upgrade'),
            'isLocked' => $updateManager->isLocked(),
            'allowManualUpload' => config('system.updates.allow_manual_upload', true),
            'remoteMetadata' => $remote,
        ]);
    }

    public function checkUpdates(SystemVersionService $versionService)
    {
        abort_unless(can_access_system('system.update.view'), 403);

        $remote = $versionService->checkRemote();

        if ($remote) {
            Alert::toast('Update check completed.', 'success');
        } else {
            Alert::toast('Unable to reach update server or check is disabled.', 'warning');
        }

        return redirect()->route('system.update.index');
    }

    public function validatePackage(Request $request, UpdateManager $updateManager, UpdateBackupManager $backupManager)
    {
        abort_unless(can_access_system('system.update.upload'), 403);

        $maxMb = (int) config('system.updates.max_upload_size', 512);

        $request->validate([
            'update_package' => "required|file|mimes:zip|max:{$maxMb}",
        ]);

        $file = $request->file('update_package');
        $storedPath = $backupManager->makeTempPackagePath($file->getClientOriginalName());
        $file->move(dirname($storedPath), basename($storedPath));

        try {
            $result = $updateManager->validatePackage($storedPath);

            session([
                'system_update_validation' => $result,
                'system_update_package_path' => $storedPath,
            ]);

            return view('system.update.validation', [
                'validation' => $result,
                'packageName' => basename($storedPath),
            ]);
        } catch (\Throwable $e) {
            @unlink($storedPath);

            Alert::toast($e->getMessage(), 'error');

            return redirect()->route('system.update.index');
        }
    }

    public function install(Request $request, UpdateManager $updateManager)
    {
        abort_unless(can_access_system('system.update.install'), 403);

        $packagePath = session('system_update_package_path');

        if (! $packagePath || ! file_exists($packagePath)) {
            Alert::toast('No validated update package found. Please upload and validate again.', 'error');

            return redirect()->route('system.update.index');
        }

        try {
            $update = $updateManager->install($packagePath);
            session()->forget(['system_update_validation', 'system_update_package_path']);
            @unlink($packagePath);

            Alert::toast("System updated successfully to version {$update->to_version}.", 'success');

            return redirect()->route('system.update.history')->with('update_logs', $updateManager->logger()->entries());
        } catch (SystemUpdateException|\Throwable $e) {
            Alert::toast('Update failed: '.$e->getMessage(), 'error');

            return redirect()->route('system.update.index')
                ->with('update_logs', $updateManager->logger()->entries());
        }
    }

    public function history()
    {
        abort_unless(can_access_system('system.update.history'), 403);

        $updates = SystemUpdate::query()
            ->with('executor')
            ->latest()
            ->paginate(20);

        return view('system.update.history', compact('updates'));
    }

    public function show(SystemUpdate $systemUpdate)
    {
        abort_unless(can_access_system('system.update.history'), 403);

        $systemUpdate->load(['logs', 'executor']);

        return view('system.update.show', ['update' => $systemUpdate]);
    }

    public function rollback(SystemUpdate $systemUpdate, UpdateManager $updateManager)
    {
        abort_unless(can_access_system('system.update.rollback'), 403);

        try {
            $updateManager->rollback($systemUpdate);
            Alert::toast('Update rolled back successfully.', 'success');
        } catch (\Throwable $e) {
            Alert::toast('Rollback failed: '.$e->getMessage(), 'error');
        }

        return redirect()->route('system.update.history');
    }
}
