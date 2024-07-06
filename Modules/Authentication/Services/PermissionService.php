<?php

namespace Modules\Authentication\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Authentication\Interfaces\PermissionInterface;
use Spatie\Permission\Models\Permission;

final class PermissionService implements PermissionInterface
{
    public function __construct(
        protected Permission $model
    ) {}

    public function index($request, int $per_page = 10): LengthAwarePaginator
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
            // 🔍 Search
            ->when($request->search, function ($query) use ($request) {
                $query->where('name', 'like', $request->search.'%');
            })

            ->orderBy($orderColumn, $orderDirection)
            ->paginate($per_page);
    }

    public function getAll($request): Collection
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
            ->when($request->search, function ($query) use ($request) {
                $query->where('name', 'like', $request->search.'%');
            })
            ->orderBy($orderColumn, $orderDirection)
            ->get();
    }

    public function getById(int $id): ?Permission
    {
        return $this->model::where('id', $id)->first();
    }

    public function create(array $data)
    {
        return $this->model::create($data);
    }

    public function update(int $id, array $data): ?Permission
    {
        $model = $this->model::where('id', $id)->first();
        $model->update($data);

        return $model;
    }

    public function delete(int $id): int
    {
        $model = $this->model::where('id', $id)->first();

        return $model->delete();
    }
}
