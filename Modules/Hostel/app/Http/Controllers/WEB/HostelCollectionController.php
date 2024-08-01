<?php

namespace Modules\Hostel\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Traits\HasPermissionWebTrait;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Modules\Accounting\Models\AccountingFund;
use Modules\Accounting\Models\AccountingLedger;
use Modules\Hostel\Http\Requests\HostelCollection\HostelCollectionStoreRequest;
use Modules\Hostel\Models\HostelCollection;
use Modules\Hostel\Models\RoomMember;
use Modules\Hostel\Services\HostelCollectionService;
use RealRashid\SweetAlert\Facades\Alert;

class HostelCollectionController extends Controller
{
    use HasPermissionWebTrait;

    public function __construct(private readonly HostelCollectionService $hostelCollectionService) {}

    public function index(Request $request): bool|Factory|RedirectResponse|View
    {
        $permissionResult = $this->checkPermission('hostel_collection_index');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        $currentMonth = $request->month ?: now()->format('Y-m');
        $search = trim((string) $request->get('search', ''));

        $query = RoomMember::with([
            'student.user',
            'room.hostelCategory',
            'bed',
            'collections' => fn ($q) => $q->where('month', $currentMonth),
        ])
            ->withSum(['collections as total_outstanding_due' => fn ($q) => $q->where('total_due', '>', 0)], 'total_due')
            ->where('status', 'active');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereHas('student.user', fn ($u) => $u->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('room', fn ($r) => $r->where('room_number', 'like', "%{$search}%"));
            });
        }

        $members = $query->get();

        return view('hostel::hostel_collection.index', compact('members', 'currentMonth', 'search'));
    }

    public function create(int $roomMemberId): bool|Factory|RedirectResponse|View
    {
        $permissionResult = $this->checkPermission('hostel_collection_create');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        $roomMember = RoomMember::with(['student.user', 'room.hostelCategory', 'bed'])->findOrFail($roomMemberId);
        $ledgers = AccountingLedger::all();
        $funds = AccountingFund::all();

        $feeConfig = $this->hostelCollectionService->getFeeConfig(
            $roomMember->room->hostel_category_id,
            (int) get_option('academic_year')
        );

        return view('hostel::hostel_collection.create', compact('roomMember', 'ledgers', 'funds', 'feeConfig'));
    }

    public function store(HostelCollectionStoreRequest $request): Redirector|RedirectResponse
    {
        $permissionResult = $this->checkPermission('hostel_collection_create');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        try {
            $collection = $this->hostelCollectionService->createCollection($request->validated());

            Alert::toast('Hostel fee collected successfully.', 'success');

            return redirect()->route('hostel-collections.invoice', $collection->id);
        } catch (\Throwable $e) {
            Alert::error('Error', $e->getMessage());

            return back()->withInput();
        }
    }

    public function invoice(int $id): Factory|View
    {
        $collection = HostelCollection::with(['student.user', 'roomMember.room', 'details'])->findOrFail($id);

        return view('hostel::hostel_collection.invoice', compact('collection'));
    }
}
