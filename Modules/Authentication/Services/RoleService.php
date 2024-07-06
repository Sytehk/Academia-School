<?php

namespace Modules\Authentication\Services;

use Illuminate\Support\Facades\DB;
use Modules\Authentication\Interfaces\RoleInterface;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class RoleService implements RoleInterface
{
    public function __construct(
        protected Role $model
    ) {}

    public function index($request, int $per_page = 50)
    {
        $orderColumn = request('sort_column', 'id');
        $orderDirection = request('sort_direction', 'desc');

        if (! in_array($orderColumn, ['id', 'name', 'created_at'])) {
            $orderColumn = 'id';
        }
        if (! in_array($orderDirection, ['asc', 'desc'])) {
            $orderDirection = 'desc';
        }

        return $this->model::query()
            ->where('institute_id', get_institute_id())
            ->with(['permissions'])
            ->when($request->search, function ($query) use ($request) {
                $query->where('name', 'like', $request->search.'%');
            })
            ->orderBy($orderColumn, $orderDirection)
            ->paginate($per_page);
    }

    public function getAll($request)
    {
        $orderColumn = request('sort_column', 'id');
        $orderDirection = request('sort_direction', 'desc');

        if (! in_array($orderColumn, ['id', 'name', 'created_at'])) {
            $orderColumn = 'id';
        }
        if (! in_array($orderDirection, ['asc', 'desc'])) {
            $orderDirection = 'desc';
        }

        return $this->model::query()
            ->where('created_by', getUserId())
            ->when($request->search, function ($query) use ($request) {
                $query->where('name', 'like', $request->search.'%');
            })
            ->orderBy($orderColumn, $orderDirection)
            ->get();
    }

    public function getById(int $id): ?Role
    {
        return $this->model::where('id', $id)
            ->where('institute_id', get_institute_id())
            ->first();
    }

    public function create(array $data): Role
    {
        $role = $this->model::create([
            'name' => $data['name'],
            'guard_name' => 'web',
            'description' => $data['description'] ?? null,
            'institute_id' => get_institute_id(),
            'branch_id' => get_branch_id(),
            'created_by' => getUserId(),
        ]);
        $role->permissions()->sync($data['permissions']);

        return $role;
    }

    public function update(int $id, array $data): ?Role
    {
        $model = $this->getById($id);
        if (! $model) {
            return null;
        }
        $model->update($data);
        if (! empty($data['permissions'])) {
            $permissions = Permission::whereIn('id', $data['permissions'])->pluck('name')->toArray();
            $model->syncPermissions($permissions);
        }

        return $model;
    }

    public function delete(int $id): int
    {
        return DB::table('roles')
            ->where('id', $id)
            ->where('institute_id', get_institute_id())
            ->delete();
    }

    public function userRolePermissions($user, $role): array
    {
        $permissions = $role->permissions->map(function ($permission) {
            return $permission->name;
        });

        $data = [
            'user_id' => $user->id,
            'username' => $user->name,
            'role_name' => $role->name,
            'permissions' => $permissions,
        ];

        return $data;
    }
}
