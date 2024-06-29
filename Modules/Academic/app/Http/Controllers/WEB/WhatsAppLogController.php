<?php

namespace Modules\Academic\Http\Controllers\WEB;

use App\Exports\WhatsAppLogExport;
use App\Http\Controllers\Controller;
use App\Services\WhatsAppService;
use App\Traits\HasPermissionWebTrait;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Academic\Models\WhatsAppLog;
use Pdf;
use RealRashid\SweetAlert\Facades\Alert;

class WhatsAppLogController extends Controller
{
    use HasPermissionWebTrait;

    private function filteredLogs(Request $request)
    {
        $query = WhatsAppLog::with('student.user')
            ->where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id());

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->student_id) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->from && $request->to) {
            $query->whereBetween('created_at', [
                Carbon::parse($request->from)->startOfDay(),
                Carbon::parse($request->to)->endOfDay(),
            ]);
        }

        return $query->orderByDesc('id');
    }

    public function index(Request $request): bool|Factory|RedirectResponse|View
    {
        $permissionResult = $this->checkPermission('whats_app_log_index');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        $logs = $this->filteredLogs($request)->paginate(20)->withQueryString();

        return view('academic::whats_app_logs.index', compact('logs'));
    }

    public function exportPdf(Request $request)
    {
        $permissionResult = $this->checkPermission('whats_app_log_export');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        $logs = $this->filteredLogs($request)->get();

        return Pdf::loadView('academic::whats_app_logs.pdf', compact('logs'))
            ->setPaper('a4', 'landscape')
            ->download('whatsapp-logs-'.now()->format('Y-m-d').'.pdf');
    }

    public function exportExcel(Request $request)
    {
        $permissionResult = $this->checkPermission('whats_app_log_export');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        $logs = $this->filteredLogs($request)->get();

        return Excel::download(new WhatsAppLogExport($logs), 'whatsapp-logs-'.now()->format('Y-m-d').'.xlsx');
    }

    public function testConnection(): RedirectResponse
    {
        // $permissionResult = $this->checkPermission('whats_app_setting_update');
        // if ($permissionResult !== true) {
        //     return $permissionResult;
        // }

        $result = app(WhatsAppService::class)->testConnection();

        Alert::success('Success', $result['message']);

        return back();
    }
}
