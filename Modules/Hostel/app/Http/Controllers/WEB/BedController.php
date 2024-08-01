<?php

namespace Modules\Hostel\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Traits\HasPermissionWebTrait;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Modules\Hostel\Models\Bed;
use Modules\Hostel\Models\Room;
use RealRashid\SweetAlert\Facades\Alert;

class BedController extends Controller
{
    use HasPermissionWebTrait;

    public function index(): bool|Factory|RedirectResponse|View
    {
        $permissionResult = $this->checkPermission('bed_index');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        $beds = Bed::with('room')->orderByDesc('id')->paginate(20);

        return view('hostel::bed.index', compact('beds'));
    }

    public function create(): bool|Factory|RedirectResponse|View
    {
        $permissionResult = $this->checkPermission('bed_create');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        $rooms = Room::all();

        return view('hostel::bed.create', compact('rooms'));
    }

    public function store(Request $request): Redirector|RedirectResponse
    {
        $permissionResult = $this->checkPermission('bed_create');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        $data = $request->validate([
            'room_id' => 'required|integer|exists:rooms,id',
            'bed_number' => 'required|string|max:50',
        ]);

        $data['institute_id'] = get_institute_id();
        $data['branch_id'] = get_branch_id();
        $data['status'] = 'vacant';

        Bed::create($data);

        Alert::toast('Bed created successfully.', 'success');

        return redirect()->route('beds.index');
    }

    public function edit(int $id): bool|Factory|RedirectResponse|View
    {
        $permissionResult = $this->checkPermission('bed_update');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        $bed = Bed::findOrFail($id);
        $rooms = Room::all();

        return view('hostel::bed.edit', compact('bed', 'rooms'));
    }

    public function update(Request $request, int $id): Redirector|RedirectResponse
    {
        $permissionResult = $this->checkPermission('bed_update');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        $data = $request->validate([
            'room_id' => 'required|integer|exists:rooms,id',
            'bed_number' => 'required|string|max:50',
            'status' => 'required|in:vacant,occupied',
        ]);

        Bed::findOrFail($id)->update($data);

        Alert::toast('Bed updated successfully.', 'success');

        return redirect()->route('beds.index');
    }

    public function destroy(int $id): RedirectResponse
    {
        $permissionResult = $this->checkPermission('bed_delete');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        Bed::findOrFail($id)->delete();

        Alert::toast('Bed deleted successfully.', 'success');

        return back();
    }
}
