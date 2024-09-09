<?php

namespace Modules\Transport\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Traits\HasPermissionWebTrait;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Modules\Transport\Models\Bus;
use Modules\Transport\Models\Helper;
use RealRashid\SweetAlert\Facades\Alert;

class HelperController extends Controller
{
    use HasPermissionWebTrait;

    private function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:500',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:30',
            'assigned_bus_id' => 'nullable|integer|exists:buses,id',
        ];
    }

    public function index(): bool|Factory|RedirectResponse|View
    {
        $permissionResult = $this->checkPermission('helper_index');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        $helpers = Helper::with('bus')->orderByDesc('id')->paginate(20);

        return view('transport::helper.index', compact('helpers'));
    }

    public function create(): bool|Factory|RedirectResponse|View
    {
        $permissionResult = $this->checkPermission('helper_create');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        $buses = Bus::all();

        return view('transport::helper.create', compact('buses'));
    }

    public function store(Request $request): Redirector|RedirectResponse
    {
        $permissionResult = $this->checkPermission('helper_create');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        $data = $request->validate($this->rules());
        $data['institute_id'] = get_institute_id();
        $data['branch_id'] = get_branch_id();
        $data['status'] = $request->status ?? 1;

        Helper::create($data);

        Alert::toast('Helper created successfully.', 'success');

        return redirect()->route('helpers.index');
    }

    public function edit(int $id): bool|Factory|RedirectResponse|View
    {
        $permissionResult = $this->checkPermission('helper_update');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        $helper = Helper::findOrFail($id);
        $buses = Bus::all();

        return view('transport::helper.edit', compact('helper', 'buses'));
    }

    public function update(Request $request, int $id): Redirector|RedirectResponse
    {
        $permissionResult = $this->checkPermission('helper_update');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        $data = $request->validate($this->rules());
        $data['status'] = $request->status ?? 1;

        Helper::findOrFail($id)->update($data);

        Alert::toast('Helper updated successfully.', 'success');

        return redirect()->route('helpers.index');
    }

    public function destroy(int $id): RedirectResponse
    {
        $permissionResult = $this->checkPermission('helper_delete');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        Helper::findOrFail($id)->delete();

        Alert::toast('Helper deleted successfully.', 'success');

        return back();
    }
}
