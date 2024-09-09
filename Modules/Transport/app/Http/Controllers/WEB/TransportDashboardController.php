<?php

namespace Modules\Transport\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Traits\HasPermissionWebTrait;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Modules\Transport\Models\Bus;
use Modules\Transport\Models\BusSeat;
use Modules\Transport\Models\TransportCollection;
use Modules\Transport\Models\TransportMember;

class TransportDashboardController extends Controller
{
    use HasPermissionWebTrait;

    public function index(): bool|Factory|RedirectResponse|View
    {
        $permissionResult = $this->checkPermission('transport_panel');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        $activeBuses = Bus::where('status', 1)->count();
        $studentsAssigned = TransportMember::where('status', 1)->count();
        $vacantSeats = BusSeat::where('status', 'vacant')->count();
        $totalSeats = BusSeat::count();

        $todayCollection = TransportCollection::whereDate('invoice_date', now()->toDateString())->sum('total_paid');
        $totalDue = TransportCollection::sum('total_due');

        return view('transport::dashboard.index', compact(
            'activeBuses',
            'studentsAssigned',
            'vacantSeats',
            'totalSeats',
            'todayCollection',
            'totalDue'
        ));
    }
}
