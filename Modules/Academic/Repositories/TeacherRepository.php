<?php

declare(strict_types=1);

namespace Modules\Academic\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Academic\Models\Teacher;

class TeacherRepository implements BaseRepositoryInterface
{
    public function all(): Collection
    {
        return Teacher::all();
    }

    public function paginate(int $perPage = 100, array $filter = []): LengthAwarePaginator
    {
        $query = Teacher::query();

        return $query->where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->paginate($perPage);
    }

    public function create(array $data): ?Teacher
    {
        return Teacher::create($data);
    }

    public function update(array $data, int $id): mixed
    {
        $teacher = $this->show($id);

        return $teacher?->update($data) ?? false;
    }

    public function delete(int $id): int
    {
        return Teacher::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->where('id', $id)
            ->delete();
    }

    public function show(int $id): ?Teacher
    {
        return Teacher::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->find($id);
    }
}
