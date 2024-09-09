<?php

namespace Modules\Transport\Http\Controllers\WEB;

use App\Exports\TransportCollectionExport;
use App\Http\Controllers\Controller;
use App\Traits\HasPermissionWebTrait;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Transport\Models\TransportCollection;
use Modules\Transport\Models\TransportMember;
use Pdf;

class TransportFeeReportController extends Controller
{
    use HasPermissionWebTrait;

    private function collectionQuery(Request $request)
    {
        $query = TransportCollection::with(['student.user', 'transportMember.route', 'transportMember.stop']);

        if ($sessionId = get_option('academic_year')) {
            $query->where('session_id', $sessionId);
        }

        if ($request->from && $request->to) {
            $query->whereBetween('invoice_date', [
                Carbon::parse($request->from)->toDateString(),
                Carbon::parse($request->to)->toDateString(),
            ]);
        }

        if ($request->route_id) {
            $query->whereHas('transportMember', fn ($q) => $q->where('assigned_route_id', $request->route_id));
        }

        return $query->orderByDesc('invoice_date');
    }

    public function collectionReport(Request $request): bool|Factory|View
    {
        $permissionResult = $this->checkPermission('transport_report_index');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        if (! get_option('academic_year')) {
            session()->flash('error', _lang('Active academic session is required for this report.'));

            return view('transport::reports.collection', ['collections' => collect(), 'totalCollected' => 0]);
        }

        $collections = $this->collectionQuery($request)->get();
        $totalCollected = $collections->sum('total_paid');

        return view('transport::reports.collection', compact('collections', 'totalCollected'));
    }

    public function dueReport(Request $request): bool|Factory|View
    {
        $permissionResult = $this->checkPermission('transport_report_index');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        if (! get_option('academic_year')) {
            session()->flash('error', _lang('Active academic session is required for this report.'));

            return view('transport::reports.due', ['members' => collect()]);
        }

        $members = TransportMember::with(['student.user', 'route', 'stop', 'collections' => function ($query) {
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

        return view('transport::reports.due', compact('members'));
    }

    public function routeWiseReport(Request $request): bool|Factory|View
    {
        $permissionResult = $this->checkPermission('transport_report_index');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        $collections = $this->collectionQuery($request)->get()
            ->groupBy(fn ($c) => $c->transportMember?->route?->route_name ?? 'Unassigned');

        return view('transport::reports.route-wise', compact('collections'));
    }

    public function vehicleWiseReport(Request $request): bool|Factory|View
    {
        $permissionResult = $this->checkPermission('transport_report_index');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        $collections = $this->collectionQuery($request)->get()
            ->groupBy(fn ($c) => $c->transportMember?->route?->bus?->bus_number ?? 'Unassigned');

        return view('transport::reports.vehicle-wise', compact('collections'));
    }

    public function exportPdf(Request $request)
    {
        $permissionResult = $this->checkPermission('transport_report_export');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        $collections = $this->collectionQuery($request)->get();

        return Pdf::loadView('transport::reports.pdf', compact('collections'))
            ->setPaper('a4', 'landscape')
            ->download('transport-collection-report-'.now()->format('Y-m-d').'.pdf');
    }

    public function exportExcel(Request $request)
    {
        $permissionResult = $this->checkPermission('transport_report_export');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        $collections = $this->collectionQuery($request)->get();

        return Excel::download(new TransportCollectionExport($collections), 'transport-collection-report-'.now()->format('Y-m-d').'.xlsx');
    }
}
