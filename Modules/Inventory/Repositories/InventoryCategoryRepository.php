<?php

namespace Modules\Inventory\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Inventory\Models\InventoryCategory;

class InventoryCategoryRepository implements BaseRepositoryInterface
{
    public function all(): Collection
    {
        return InventoryCategory::orderByDesc('id')->get();
    }

    public function paginate(int $perPage = 20, array $filter = []): LengthAwarePaginator
    {
        $query = InventoryCategory::query()->orderByDesc('id');

        if (! empty($filter['search'])) {
            $query->where('name', 'like', '%'.$filter['search'].'%');
        }

        return $query->paginate($perPage);
    }

    public function create(array $data): ?InventoryCategory
    {
        return InventoryCategory::create($data);
    }

    public function update(array $data, int $id): mixed
    {
        return InventoryCategory::find($id)?->update($data);
    }

    public function delete(int $id): int
    {
        return InventoryCategory::destroy($id);
    }

    public function show(int $id): ?InventoryCategory
    {
        return InventoryCategory::find($id);
    }
}
