<?php

namespace Modules\Hostel\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Traits\HasPermissionWebTrait;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Modules\Hostel\Models\HostelBuilding;
use Modules\Hostel\Models\HostelFloor;
use RealRashid\SweetAlert\Facades\Alert;

class HostelFloorController extends Controller
{
    use HasPermissionWebTrait;

    public function index(): bool|Factory|RedirectResponse|View
    {
        $permissionResult = $this->checkPermission('hostel_floor_index');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        $floors = HostelFloor::with('building')->orderByDesc('id')->paginate(20);

        return view('hostel::hostel_floor.index', compact('floors'));
    }

    public function create(): bool|Factory|RedirectResponse|View
    {
        $permissionResult = $this->checkPermission('hostel_floor_create');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        $buildings = HostelBuilding::all();

        return view('hostel::hostel_floor.create', compact('buildings'));
    }

    public function store(Request $request): Redirector|RedirectResponse
    {
        $permissionResult = $this->checkPermission('hostel_floor_create');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        $data = $request->validate([
            'building_id' => 'nullable|integer|exists:hostel_buildings,id',
            'floor_name' => 'required|string|max:255',
        ]);
        $data['building_id'] = $data['building_id'] ?: null;

        $data['institute_id'] = get_institute_id();
        $data['branch_id'] = get_branch_id();

        HostelFloor::create($data);

        Alert::toast('Floor created successfully.', 'success');

        return redirect()->route('hostel-floors.index');
    }

    public function edit(int $id): bool|Factory|RedirectResponse|View
    {
        $permissionResult = $this->checkPermission('hostel_floor_update');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        $floor = HostelFloor::findOrFail($id);
        $buildings = HostelBuilding::all();

        return view('hostel::hostel_floor.edit', compact('floor', 'buildings'));
    }

    public function update(Request $request, int $id): Redirector|RedirectResponse
    {
        $permissionResult = $this->checkPermission('hostel_floor_update');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        $data = $request->validate([
            'building_id' => 'nullable|integer|exists:hostel_buildings,id',
            'floor_name' => 'required|string|max:255',
        ]);
        $data['building_id'] = $data['building_id'] ?: null;

        HostelFloor::findOrFail($id)->update($data);

        Alert::toast('Floor updated successfully.', 'success');

        return redirect()->route('hostel-floors.index');
    }

    public function destroy(int $id): RedirectResponse
    {
        $permissionResult = $this->checkPermission('hostel_floor_delete');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        HostelFloor::findOrFail($id)->delete();

        Alert::toast('Floor deleted successfully.', 'success');

        return back();
    }
}
