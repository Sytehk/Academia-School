<?php

namespace Modules\Hostel\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Traits\HasPermissionWebTrait;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Modules\Hostel\Models\Bed;
use Modules\Hostel\Models\HostelCollection;
use Modules\Hostel\Models\RoomMember;

class HostelDashboardController extends Controller
{
    use HasPermissionWebTrait;

    public function index(): bool|Factory|RedirectResponse|View
    {
        $permissionResult = $this->checkPermission('hostel_panel');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        $totalBeds = Bed::count();
        $vacantBeds = Bed::where('status', 'vacant')->count();
        $occupiedBeds = $totalBeds - $vacantBeds;
        $occupancyRate = $totalBeds > 0 ? round(($occupiedBeds / $totalBeds) * 100, 1) : 0;

        $activeMembers = RoomMember::where('status', 'active')->count();

        $todayCollection = HostelCollection::whereDate('invoice_date', now()->toDateString())->sum('total_paid');
        $totalDue = HostelCollection::sum('total_due');

        return view('hostel::dashboard.index', compact(
            'totalBeds',
            'vacantBeds',
            'occupiedBeds',
            'occupancyRate',
            'activeMembers',
            'todayCollection',
            'totalDue'
        ));
    }
}
