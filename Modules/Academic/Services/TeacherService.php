<?php

declare(strict_types=1);

namespace Modules\Academic\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Academic\Models\Teacher;
use Modules\Academic\Repositories\TeacherRepository;

class TeacherService
{
    public function __construct(
        private readonly TeacherRepository $teacherRepository
    ) {}

    public function getTeachers(array $filter = [], int $perPage = 100): LengthAwarePaginator
    {
        return $this->teacherRepository->paginate($perPage, $filter);
    }

    public function findTeacherById(int $id): ?Teacher
    {
        return $this->teacherRepository->show((int) $id);
    }

    public function createTeacher(array $data): ?Teacher
    {
        $data['institute_id'] = get_institute_id();
        $data['branch_id'] = get_branch_id();

        return $this->teacherRepository->create($data);
    }

    public function updateTeacher(array $data, int $id): mixed
    {
        return $this->teacherRepository->update($data, $id);
    }

    public function deleteTeacherById(int $id): int
    {
        return $this->teacherRepository->delete($id);
    }
}
