<?php

declare(strict_types=1);

namespace Modules\Academic\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Academic\Models\Department;

class DepartmentRepository implements BaseRepositoryInterface
{
    public function all(): Collection
    {
        return Department::all();
    }

    public function paginate(int $perPage = 100, array $filter = []): LengthAwarePaginator
    {
        $query = Department::query();

        return $query->orderBy('id', 'asc')->where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->paginate($perPage);
    }

    public function create(array $data): ?Department
    {
        return Department::create($data);
    }

    public function update(array $data, int $id): mixed
    {
        return Department::find($id)->update($data);
    }

    public function delete(int $id): int
    {
        return Department::destroy($id);
    }

    public function show(int $id): ?Department
    {
        return Department::find($id);
    }
}
