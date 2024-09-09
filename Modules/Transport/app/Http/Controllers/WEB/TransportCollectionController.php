<?php

namespace Modules\Transport\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Traits\HasPermissionWebTrait;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Modules\Accounting\Models\AccountingFund;
use Modules\Accounting\Models\AccountingLedger;
use Modules\Transport\Http\Requests\TransportCollection\TransportCollectionStoreRequest;
use Modules\Transport\Models\TransportCollection;
use Modules\Transport\Models\TransportMember;
use Modules\Transport\Services\TransportCollectionService;
use RealRashid\SweetAlert\Facades\Alert;

class TransportCollectionController extends Controller
{
    use HasPermissionWebTrait;

    public function __construct(private readonly TransportCollectionService $transportCollectionService) {}

    public function index(Request $request): bool|Factory|RedirectResponse|View
    {
        $permissionResult = $this->checkPermission('transport_collection_index');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        $currentMonth = $request->month ?: now()->format('Y-m');

        $members = TransportMember::with(['student.user', 'route', 'stop', 'collections' => function ($query) use ($currentMonth) {
            $query->where('month', $currentMonth);
        }])
            ->where('status', 1)
            ->when($request->route_id, fn ($q) => $q->where('assigned_route_id', $request->route_id))
            ->get();

        return view('transport::transport_collection.index', compact('members', 'currentMonth'));
    }

    public function create(int $transportMemberId): bool|Factory|RedirectResponse|View
    {
        $permissionResult = $this->checkPermission('transport_collection_create');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        $transportMember = TransportMember::with(['student.user', 'route', 'stop'])->findOrFail($transportMemberId);
        $ledgers = AccountingLedger::all();
        $funds = AccountingFund::all();

        $feeConfig = $this->transportCollectionService->getFeeConfig(
            $transportMember->assigned_route_id,
            $transportMember->assigned_stop_id,
            (int) get_option('academic_year')
        );

        $defaultFee = $feeConfig->monthly_fee ?? $transportMember->fare_amount;

        return view('transport::transport_collection.create', compact('transportMember', 'ledgers', 'funds', 'feeConfig', 'defaultFee'));
    }

    public function store(TransportCollectionStoreRequest $request): Redirector|RedirectResponse
    {
        $permissionResult = $this->checkPermission('transport_collection_create');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        try {
            $collection = $this->transportCollectionService->createCollection($request->validated());

            Alert::toast('Transport fee collected successfully.', 'success');

            return redirect()->route('transport-collections.invoice', $collection->id);
        } catch (\Throwable $e) {
            Alert::error('Error', $e->getMessage());

            return back()->withInput();
        }
    }

    public function invoice(int $id): Factory|View
    {
        $collection = TransportCollection::with(['student.user', 'transportMember.route', 'transportMember.stop'])->findOrFail($id);

        return view('transport::transport_collection.invoice', compact('collection'));
    }
}
