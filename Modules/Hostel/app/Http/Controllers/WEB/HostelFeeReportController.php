<?php

namespace Modules\Hostel\Http\Controllers\WEB;

use App\Exports\HostelCollectionExport;
use App\Http\Controllers\Controller;
use App\Traits\HasPermissionWebTrait;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Hostel\Models\HostelCollection;
use Modules\Hostel\Models\RoomMember;
use Pdf;

class HostelFeeReportController extends Controller
{
    use HasPermissionWebTrait;

    private function collectionQuery(Request $request)
    {
        $query = HostelCollection::with(['student.user', 'roomMember.room.hostel', 'roomMember.room.building']);

        if ($sessionId = get_option('academic_year')) {
            $query->where('session_id', $sessionId);
        }

        if ($request->from && $request->to) {
            $query->whereBetween('invoice_date', [
                Carbon::parse($request->from)->toDateString(),
                Carbon::parse($request->to)->toDateString(),
            ]);
        }

        return $query->orderByDesc('invoice_date');
    }

    public function collectionReport(Request $request): bool|Factory|View
    {
        $permissionResult = $this->checkPermission('hostel_report_index');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        if (! get_option('academic_year')) {
            session()->flash('error', _lang('Active academic session is required for this report.'));

            return view('hostel::reports.collection', ['collections' => collect(), 'totalCollected' => 0]);
        }

        $collections = $this->collectionQuery($request)->get();
        $totalCollected = $collections->sum('total_paid');

        return view('hostel::reports.collection', compact('collections', 'totalCollected'));
    }

    public function dueReport(Request $request): bool|Factory|View
    {
        $permissionResult = $this->checkPermission('hostel_report_index');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        if (! get_option('academic_year')) {
            session()->flash('error', _lang('Active academic session is required for this report.'));

            return view('hostel::reports.due', ['members' => collect()]);
        }

        $members = RoomMember::with(['student.user', 'room', 'collections' => function ($query) {
            if ($sessionId = get_option('academic_year')) {
                $query->where('session_id', $sessionId);
            }
            $query->where('total_due', '>', 0);
        }])
            ->whereHas('collections', function ($q) {
                if ($sessionId = get_option('academic_year')) {
                    $q->where('session_id', $sessionId);
                }
                $q->where('total_due', '>', 0);
            })
            ->get();

        return view('hostel::reports.due', compact('members'));
    }

    public function hostelWiseReport(Request $request): bool|Factory|View
    {
        $permissionResult = $this->checkPermission('hostel_report_index');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        $collections = $this->collectionQuery($request)->get()
            ->groupBy(fn ($c) => $c->roomMember?->room?->hostel?->hostel_name ?? 'Unassigned');

        return view('hostel::reports.hostel-wise', compact('collections'));
    }

    public function buildingWiseReport(Request $request): bool|Factory|View
    {
        $permissionResult = $this->checkPermission('hostel_report_index');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        $collections = $this->collectionQuery($request)->get()
            ->groupBy(fn ($c) => $c->roomMember?->room?->building?->building_name ?? 'Unassigned');

        return view('hostel::reports.building-wise', compact('collections'));
    }

    public function roomWiseReport(Request $request): bool|Factory|View
    {
        $permissionResult = $this->checkPermission('hostel_report_index');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        $collections = $this->collectionQuery($request)->get()
            ->groupBy(fn ($c) => $c->roomMember?->room?->room_number ?? 'Unassigned');

        return view('hostel::reports.room-wise', compact('collections'));
    }

    public function exportPdf(Request $request)
    {
        $permissionResult = $this->checkPermission('hostel_report_export');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        $collections = $this->collectionQuery($request)->get();

        return Pdf::loadView('hostel::reports.pdf', compact('collections'))
            ->setPaper('a4', 'landscape')
            ->download('hostel-collection-report-'.now()->format('Y-m-d').'.pdf');
    }

    public function exportExcel(Request $request)
    {
        $permissionResult = $this->checkPermission('hostel_report_export');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        $collections = $this->collectionQuery($request)->get();

        return Excel::download(new HostelCollectionExport($collections), 'hostel-collection-report-'.now()->format('Y-m-d').'.xlsx');
    }
}
