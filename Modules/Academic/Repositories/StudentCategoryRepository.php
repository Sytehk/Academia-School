<?php

declare(strict_types=1);

namespace Modules\Academic\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Academic\Models\StudentCategory;

class StudentCategoryRepository implements BaseRepositoryInterface
{
    public function all(): Collection
    {
        return StudentCategory::where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->orderBy('id', 'ASC')->get();
    }

    public function paginate(int $perPage = 100, array $filter = []): LengthAwarePaginator
    {
        $query = StudentCategory::query();

        return $query->orderBy('id', 'asc')->where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->paginate($perPage);
    }

    public function create(array $data): ?StudentCategory
    {
        return StudentCategory::create($data);
    }

    public function update(array $data, int $id): mixed
    {
        return StudentCategory::find($id)->update($data);
    }

    public function delete(int $id): int
    {
        return StudentCategory::destroy($id);
    }

    public function show(int $id): ?StudentCategory
    {
        return StudentCategory::find($id);
    }
}
