<?php

namespace Modules\SystemConfiguration\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Modules\SystemConfiguration\Services\UserLogService;
use RealRashid\SweetAlert\Facades\Alert;

class UserLogsController extends Controller
{
    public function __construct(private readonly UserLogService $userLogService) {}

    public function index(): View|Factory
    {
        return view('systemconfiguration::user_logs.index', [
            'userLogs' => $this->userLogService->getUserLogs(),
        ]);
    }

    public function destroy(int $id): Redirector|RedirectResponse
    {
        $userLogs = $this->userLogService->deleteUserLogById($id);

        if (! $userLogs) {
            Alert::error('Error', _lang('Failed to delete records unsuccessfully'));

            return redirect('user-logs');
        }

        Alert::toast('User Activity has been deleted.', 'success');

        return redirect('user-logs');
    }
}
