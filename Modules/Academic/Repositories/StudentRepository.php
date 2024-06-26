<?php

declare(strict_types=1);

namespace Modules\Academic\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Academic\Models\Student;

class StudentRepository implements BaseRepositoryInterface
{
    public function all(): Collection
    {
        return Student::where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->get();
    }

    public function paginate(int $perPage = 100, array $filter = []): LengthAwarePaginator
    {
        $query = Student::query();

        return $query->where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->paginate($perPage);
    }

    public function create(array $data): ?Student
    {
        return Student::create($data);
    }

    public function show(int $id): ?Student
    {
        return Student::with('user', 'studentSession.group', 'studentCategory')
            ->where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->find($id);
    }

    public function update(array $data, int $id): mixed
    {
        $student = Student::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->find((int) $id);

        return $student?->update($data);
    }

    public function delete(int $id): int
    {
        return Student::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->where('id', $id)
            ->delete();
    }
}
