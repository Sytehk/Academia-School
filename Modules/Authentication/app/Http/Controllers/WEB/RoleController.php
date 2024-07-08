<?php

namespace Modules\Authentication\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Traits\HasPermissionWebTrait;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Modules\Authentication\Http\Requests\RoleStoreRequest;
use Modules\Authentication\Http\Requests\RoleUpdateRequest;
use Modules\Authentication\Services\PermissionService;
use Modules\Authentication\Services\RoleService;
use RealRashid\SweetAlert\Facades\Alert;

class RoleController extends Controller
{
    use HasPermissionWebTrait;

    public function __construct(
        private RoleService $service,
        private PermissionService $permissionService
    ) {}

    public function index(Request $request): bool|Factory|RedirectResponse|View
    {
        $permissionResult = $this->checkPermission('role_index');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        $roles = $this->service->getAll($request);

        return view('authentication::role.index', compact('roles'));
    }

    public function create(Request $request): bool|Factory|RedirectResponse|View
    {
        $permissionResult = $this->checkPermission('role_create');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        $permissions = $this->permissionService->getAll($request);

        $groupedPermissions = [];

        foreach ($permissions as $permission) {

            $name = $permission->name;

            // default group
            $group = $name;

            // dot format (payment_gateway_index)
            if (str_contains($name, '.')) {
                $group = explode('.', $name)[0];
            }

            // underscore format (payment_method_delete)
            elseif (str_contains($name, '_')) {
                $group = explode('_', $name)[0];
            }

            // ensure group exists
            if (! isset($groupedPermissions[$group])) {
                $groupedPermissions[$group] = [];
            }

            $groupedPermissions[$group][] = $permission;
        }

        return view('authentication::role.create', compact('groupedPermissions'));
    }

    public function store(RoleStoreRequest $request): bool|Redirector|RedirectResponse
    {
        $permissionResult = $this->checkPermission('role_create');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        DB::beginTransaction();
        try {
            $this->service->create($request->validated());
            DB::commit();
            Alert::toast('Action is successful!', 'success');

            return redirect('roles');
        } catch (\Exception $e) {
            DB::rollBack();
            Alert::error('Error', $e->getMessage());

            return redirect('roles');
        }
    }

    public function show(int $id): bool|Factory|RedirectResponse|View
    {
        if (config('global_permission.global_permission', true)) {
            $permissionResult = $this->checkPermission('role_update');
            if ($permissionResult !== true) {
                return $permissionResult;
            }
        }

        $role = $this->service->getById($id);

        return view('authentication::role.show', compact('role'));
    }

    public function edit(Request $request, int $id): bool|Factory|RedirectResponse|View
    {
        if (config('global_permission.global_permission', true)) {
            $permissionResult = $this->checkPermission('role_update');
            if ($permissionResult !== true) {
                return $permissionResult;
            }
        }

        $role = $this->service->getById($id);
        $permissions = $this->permissionService->getAll($request);
        $groupedPermissions = [];

        $rolePermissions = $role->permissions->pluck('name')->toArray(); // Fetch permissions assigned to the role

        // Group permissions by their category (e.g., role, product, etc.)
        $groupedPermissions = [];
        foreach ($permissions as $permission) {
            $group = explode('-', $permission->name)[0]; // Get group prefix (e.g., role, product, etc.)
            if (! isset($groupedPermissions[$group])) {
                $groupedPermissions[$group] = [];
            }
            $groupedPermissions[$group][] = $permission;
        }

        return view('authentication::role.edit', compact('role', 'groupedPermissions', 'rolePermissions'));
    }

    public function update(RoleUpdateRequest $request, int $id): bool|Redirector|RedirectResponse
    {
        if (config('global_permission.global_permission', true)) {
            $permissionResult = $this->checkPermission('role_update');
            if ($permissionResult !== true) {
                return $permissionResult;
            }
        }

        DB::beginTransaction();
        try {
            $this->service->update($id, $request->validated());
            DB::commit();
            Alert::toast('Action is successful!', 'success');

            return redirect('roles');
        } catch (\Exception $e) {
            DB::rollBack();
            Alert::error('Error', $e->getMessage());

            return redirect('roles');
        }
    }

    public function destroy(int $id): bool|Redirector|RedirectResponse
    {
        $permissionResult = $this->checkPermission('role_delete');
        if ($permissionResult !== true) {
            return $permissionResult;
        }

        DB::beginTransaction();
        try {
            $this->service->delete($id);
            DB::commit();
            Alert::toast('Action is successful!', 'success');

            return redirect('roles');
        } catch (\Exception $e) {
            DB::rollBack();
            Alert::error('Error', $e->getMessage());

            return redirect('roles');
        }
    }
}
