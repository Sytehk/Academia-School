<?php

namespace App\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Services\DemoModeService;
use App\Services\ModuleManagerService;
use RealRashid\SweetAlert\Facades\Alert;

class SystemModuleController extends Controller
{
    public function index(ModuleManagerService $moduleManager, DemoModeService $demoMode)
    {
        abort_unless(can_access_system('system.module.view'), 403);

        return view('system.modules.index', [
            'modules' => $moduleManager->all(),
            'demoMode' => $demoMode->enabled(),
        ]);
    }

    public function enable(string $slug, ModuleManagerService $moduleManager)
    {
        abort_unless(can_access_system('system.module.enable'), 403);

        try {
            $moduleManager->enable($slug);
            Alert::toast('Module enabled successfully.', 'success');
        } catch (\Throwable $e) {
            Alert::toast($e->getMessage(), 'error');
        }

        return redirect()->route('system.modules.index');
    }

    public function disable(string $slug, ModuleManagerService $moduleManager)
    {
        abort_unless(can_access_system('system.module.disable'), 403);

        try {
            $moduleManager->disable($slug);
            Alert::toast('Module disabled successfully.', 'success');
        } catch (\Throwable $e) {
            Alert::toast($e->getMessage(), 'error');
        }

        return redirect()->route('system.modules.index');
    }
}
