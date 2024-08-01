<?php

namespace Modules\Hostel\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Traits\HasPermissionWebTrait;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Modules\Hostel\Models\Hostel;
use Modules\Hostel\Models\HostelBuilding;
use RealRashid\SweetAlert\Facades\Alert;

class HostelBuildingController extends Controller
{
    use HasPermissionWebTrait;

    public function index(): bool|Factory|RedirectResponse|View
    {
        $permissionResult = $this->checkPermission('hostel_building_index');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        $buildings = HostelBuilding::with('hostel')->orderByDesc('id')->paginate(20);

        return view('hostel::hostel_building.index', compact('buildings'));
    }

    public function create(): bool|Factory|RedirectResponse|View
    {
        $permissionResult = $this->checkPermission('hostel_building_create');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        $hostels = Hostel::all();

        return view('hostel::hostel_building.create', compact('hostels'));
    }

    public function store(Request $request): Redirector|RedirectResponse
    {
        $permissionResult = $this->checkPermission('hostel_building_create');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        $data = $request->validate([
            'hostel_id' => 'nullable|integer|exists:hostels,id',
            'building_name' => 'required|string|max:255',
        ]);
        $data['hostel_id'] = $data['hostel_id'] ?: null;

        $data['institute_id'] = get_institute_id();
        $data['branch_id'] = get_branch_id();

        HostelBuilding::create($data);

        Alert::toast('Building created successfully.', 'success');

        return redirect()->route('hostel-buildings.index');
    }

    public function edit(int $id): bool|Factory|RedirectResponse|View
    {
        $permissionResult = $this->checkPermission('hostel_building_update');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        $building = HostelBuilding::findOrFail($id);
        $hostels = Hostel::all();

        return view('hostel::hostel_building.edit', compact('building', 'hostels'));
    }

    public function update(Request $request, int $id): Redirector|RedirectResponse
    {
        $permissionResult = $this->checkPermission('hostel_building_update');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        $data = $request->validate([
            'hostel_id' => 'nullable|integer|exists:hostels,id',
            'building_name' => 'required|string|max:255',
        ]);
        $data['hostel_id'] = $data['hostel_id'] ?: null;

        HostelBuilding::findOrFail($id)->update($data);

        Alert::toast('Building updated successfully.', 'success');

        return redirect()->route('hostel-buildings.index');
    }

    public function destroy(int $id): RedirectResponse
    {
        $permissionResult = $this->checkPermission('hostel_building_delete');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        HostelBuilding::findOrFail($id)->delete();

        Alert::toast('Building deleted successfully.', 'success');

        return back();
    }
}
