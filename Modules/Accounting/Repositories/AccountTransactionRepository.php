<?php

declare(strict_types=1);

namespace Modules\Accounting\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Accounting\Models\AccountTransaction;

class AccountTransactionRepository implements BaseRepositoryInterface
{
    public function all(): Collection
    {
        return AccountTransaction::all();
    }

    public function paginate(int $perPage = 100, array $filter = []): LengthAwarePaginator
    {
        $query = AccountTransaction::query();

        return $query->where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->paginate($perPage);
    }

    public function create(array $data): ?AccountTransaction
    {
        return AccountTransaction::create($data);
    }

    public function update(array $data, int $id): mixed
    {
        return AccountTransaction::find($id)->update($data);
    }

    public function delete(int $id): int
    {
        return AccountTransaction::destroy($id);
    }

    public function show(int $id): ?AccountTransaction
    {
        return AccountTransaction::find($id);
    }
}
