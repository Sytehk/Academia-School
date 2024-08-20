<?php

declare(strict_types=1);

namespace Modules\Finance\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Finance\Models\Fee;

class FeeRepository implements BaseRepositoryInterface
{
    public function all(): Collection
    {
        return Fee::where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->get();
    }

    public function paginate(int $perPage = 100, array $filter = []): LengthAwarePaginator
    {
        $query = Fee::query();

        return $query->where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->paginate($perPage);
    }

    public function create(array $data): ?Fee
    {
        return Fee::create($data);
    }

    public function update(array $data, int $id): mixed
    {
        return Fee::find($id)->update($data);
    }

    public function delete(int $id): int
    {
        return Fee::destroy($id);
    }

    public function show(int $id): ?Fee
    {
        return Fee::find($id);
    }
}
