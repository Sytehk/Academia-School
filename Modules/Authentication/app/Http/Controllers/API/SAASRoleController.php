<?php

namespace Modules\Authentication\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Authentication\Http\Requests\RoleStoreRequest;
use Modules\Authentication\Http\Requests\RoleUpdateRequest;
use Modules\Authentication\Models\User;
use Modules\Authentication\Services\PermissionService;
use Modules\Authentication\Services\SAASRoleService;

class SAASRoleController extends Controller
{
    public function __construct(
        private SAASRoleService $service,
        private PermissionService $permissionService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = request('per_page') ?? 10;
        $roles = $this->service->index($request, $perPage);

        if (! $roles) {
            return $this->responseError([], 'No Data Found!', 404);
        }

        return $this->responseSuccess($roles);
    }

    public function store(RoleStoreRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $this->service->create($request->validated());
            DB::commit();

            return $this->responseSuccess([], 'Role Store Success');
        } catch (Exception $e) {
            DB::rollBack();

            return $this->responseError([], $e->getMessage());
        }
    }

    public function show(int $id): JsonResponse
    {
        $role = $this->service->getById($id);

        if (! $role) {
            return $this->responseError([], 'No Data Found!', 404);
        }

        return $this->responseSuccess($role);
    }

    public function edit(Request $request, int $id): JsonResponse
    {
        $permissions = $this->permissionService->getAll($request);
        $groupedPermissions = [];

        // Group permissions by their category (e.g., role, product, etc.)
        $groupedPermissions = [];
        foreach ($permissions as $permission) {
            $group = explode('-', $permission->name)[0]; // Get group prefix (e.g., role, product, etc.)
            if (! isset($groupedPermissions[$group])) {
                $groupedPermissions[$group] = [];
            }
            $groupedPermissions[$group][] = $permission;
        }

        return $this->responseSuccess([]);
    }

    public function update(RoleUpdateRequest $request, int $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $this->service->update($id, $request->validated());
            DB::commit();

            return $this->responseSuccess([], 'Role updated successfully');
        } catch (Exception $e) {
            DB::rollBack();

            return $this->responseError([], 'Data Process Error!');
        }
    }

    public function destroy(int $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = $this->service->delete($id);
            if (! $data) {
                return $this->responseError([], 'No Data Found!', 404);
            }

            DB::commit();

            return $this->responseSuccess([], 'Data successfully delete!');
        } catch (Exception $e) {
            DB::rollBack();

            return $this->responseError([], 'Data Not Found!');
        }
    }

    public function userRolePermissions(int $userId): JsonResponse
    {
        $user = User::find($userId);

        if ($user) {
            $role = $user->roles->first();

            if ($role) {
                $data = $this->service->userRolePermissions($user, $role);

                return $this->responseSuccess($data);
            } else {
                return $this->responseError([], _lang('User has no role assigned'), 404);
            }
        }

        return $this->responseError([], 'User Not Found', 404);
    }
}
