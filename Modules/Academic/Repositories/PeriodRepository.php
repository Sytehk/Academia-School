<?php

declare(strict_types=1);

namespace Modules\Academic\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Academic\Models\Period;

class PeriodRepository implements BaseRepositoryInterface
{
    public function all(): Collection
    {
        return Period::where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->get();
    }

    public function paginate(int $perPage = 100, array $filter = []): LengthAwarePaginator
    {
        $query = Period::query();

        return $query->where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->paginate($perPage);
    }

    public function create(array $data): ?Period
    {
        return Period::create($data);
    }

    public function update(array $data, int $id): mixed
    {
        return Period::find($id)->update($data);
    }

    public function delete(int $id): int
    {
        return Period::destroy($id);
    }

    public function show(int $id): ?Period
    {
        return Period::find($id);
    }
}
