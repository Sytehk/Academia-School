<?php

namespace Modules\Transport\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Traits\HasPermissionWebTrait;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Modules\Transport\Models\VehicleCategory;
use RealRashid\SweetAlert\Facades\Alert;

class VehicleCategoryController extends Controller
{
    use HasPermissionWebTrait;

    public function index(): bool|Factory|RedirectResponse|View
    {
        $permissionResult = $this->checkPermission('vehicle_category_index');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        $vehicleCategories = VehicleCategory::orderByDesc('id')->paginate(20);

        return view('transport::vehicle_category.index', compact('vehicleCategories'));
    }

    public function create(): bool|Factory|RedirectResponse|View
    {
        $permissionResult = $this->checkPermission('vehicle_category_create');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        return view('transport::vehicle_category.create');
    }

    public function store(Request $request): Redirector|RedirectResponse
    {
        $permissionResult = $this->checkPermission('vehicle_category_create');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        $request->validate(['name' => 'required|string|max:100']);

        VehicleCategory::create([
            'institute_id' => get_institute_id(),
            'branch_id' => get_branch_id(),
            'name' => $request->name,
            'status' => $request->status ?? 1,
        ]);

        Alert::toast('Vehicle category created successfully.', 'success');

        return redirect()->route('vehicle-categories.index');
    }

    public function edit(int $id): bool|Factory|RedirectResponse|View
    {
        $permissionResult = $this->checkPermission('vehicle_category_update');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        $vehicleCategory = VehicleCategory::findOrFail($id);

        return view('transport::vehicle_category.edit', compact('vehicleCategory'));
    }

    public function update(Request $request, int $id): Redirector|RedirectResponse
    {
        $permissionResult = $this->checkPermission('vehicle_category_update');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        $request->validate(['name' => 'required|string|max:100']);

        VehicleCategory::findOrFail($id)->update([
            'name' => $request->name,
            'status' => $request->status ?? 1,
        ]);

        Alert::toast('Vehicle category updated successfully.', 'success');

        return redirect()->route('vehicle-categories.index');
    }

    public function destroy(int $id): RedirectResponse
    {
        $permissionResult = $this->checkPermission('vehicle_category_delete');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        VehicleCategory::findOrFail($id)->delete();

        Alert::toast('Vehicle category deleted successfully.', 'success');

        return back();
    }
}
