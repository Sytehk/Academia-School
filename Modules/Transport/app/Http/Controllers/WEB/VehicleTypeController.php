<?php

namespace Modules\Transport\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Traits\HasPermissionWebTrait;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Modules\Transport\Models\VehicleType;
use RealRashid\SweetAlert\Facades\Alert;

class VehicleTypeController extends Controller
{
    use HasPermissionWebTrait;

    public function index(): bool|Factory|RedirectResponse|View
    {
        $permissionResult = $this->checkPermission('vehicle_type_index');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        $vehicleTypes = VehicleType::orderByDesc('id')->paginate(20);

        return view('transport::vehicle_type.index', compact('vehicleTypes'));
    }

    public function create(): bool|Factory|RedirectResponse|View
    {
        $permissionResult = $this->checkPermission('vehicle_type_create');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        return view('transport::vehicle_type.create');
    }

    public function store(Request $request): Redirector|RedirectResponse
    {
        $permissionResult = $this->checkPermission('vehicle_type_create');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        $request->validate(['name' => 'required|string|max:100']);

        VehicleType::create([
            'institute_id' => get_institute_id(),
            'branch_id' => get_branch_id(),
            'name' => $request->name,
            'status' => $request->status ?? 1,
        ]);

        Alert::toast('Vehicle type created successfully.', 'success');

        return redirect()->route('vehicle-types.index');
    }

    public function edit(int $id): bool|Factory|RedirectResponse|View
    {
        $permissionResult = $this->checkPermission('vehicle_type_update');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        $vehicleType = VehicleType::findOrFail($id);

        return view('transport::vehicle_type.edit', compact('vehicleType'));
    }

    public function update(Request $request, int $id): Redirector|RedirectResponse
    {
        $permissionResult = $this->checkPermission('vehicle_type_update');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        $request->validate(['name' => 'required|string|max:100']);

        VehicleType::findOrFail($id)->update([
            'name' => $request->name,
            'status' => $request->status ?? 1,
        ]);

        Alert::toast('Vehicle type updated successfully.', 'success');

        return redirect()->route('vehicle-types.index');
    }

    public function destroy(int $id): RedirectResponse
    {
        $permissionResult = $this->checkPermission('vehicle_type_delete');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        VehicleType::findOrFail($id)->delete();

        Alert::toast('Vehicle type deleted successfully.', 'success');

        return back();
    }
}
