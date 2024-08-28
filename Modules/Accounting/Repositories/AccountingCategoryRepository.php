<?php

declare(strict_types=1);

namespace Modules\Accounting\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Accounting\Models\AccountingCategory;

class AccountingCategoryRepository implements BaseRepositoryInterface
{
    public function all(): Collection
    {
        return AccountingCategory::all();
    }

    public function paginate(int $perPage = 100, array $filter = []): LengthAwarePaginator
    {
        $query = AccountingCategory::query();

        return $query->orderBy('id', 'asc')->where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->paginate($perPage);
    }

    public function create(array $data): ?AccountingCategory
    {
        return AccountingCategory::create($data);
    }

    public function update(array $data, int $id): mixed
    {
        return AccountingCategory::find($id)->update($data);
    }

    public function delete(int $id): int
    {
        return AccountingCategory::destroy($id);
    }

    public function show(int $id): ?AccountingCategory
    {
        return AccountingCategory::find($id);
    }
}
