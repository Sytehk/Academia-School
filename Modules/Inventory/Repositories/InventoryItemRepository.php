<?php

namespace Modules\Inventory\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Inventory\Models\InventoryItem;

class InventoryItemRepository implements BaseRepositoryInterface
{
    public function all(): Collection
    {
        return InventoryItem::with('category')->orderByDesc('id')->get();
    }

    public function paginate(int $perPage = 20, array $filter = []): LengthAwarePaginator
    {
        $query = InventoryItem::with('category')->orderByDesc('id');

        if (! empty($filter['category_id'])) {
            $query->where('category_id', $filter['category_id']);
        }

        if (! empty($filter['search'])) {
            $query->where(function ($q) use ($filter) {
                $q->where('name', 'like', '%'.$filter['search'].'%')
                    ->orWhere('sku', 'like', '%'.$filter['search'].'%');
            });
        }

        return $query->paginate($perPage);
    }

    public function create(array $data): ?InventoryItem
    {
        return InventoryItem::create($data);
    }

    public function update(array $data, int $id): mixed
    {
        return InventoryItem::find($id)?->update($data);
    }

    public function delete(int $id): int
    {
        return InventoryItem::destroy($id);
    }

    public function show(int $id): ?InventoryItem
    {
        return InventoryItem::with('category')->find($id);
    }
}
