<?php

declare(strict_types=1);

namespace Modules\Academic\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Modules\Academic\Models\Department;
use Modules\Academic\Repositories\DepartmentRepository;

class DepartmentService
{
    public function __construct(
        private readonly DepartmentRepository $departmentRepository
    ) {}

    public function getDepartments(array $filter = [], int $perPage = 100): LengthAwarePaginator
    {
        return $this->departmentRepository->paginate($perPage, $filter);
    }

    public function findDepartmentById(int $id): ?Department
    {
        return $this->departmentRepository->show((int) $id);
    }

    public function createDepartment(array $data): ?Department
    {
        $slug = Str::slug($data['department_name']).'-'.time();
        $priority = $data['priority'] ?? 100;
        $data['priority'] = $priority;
        $data['slug'] = $slug;
        $data['institute_id'] = get_institute_id();
        $data['branch_id'] = get_branch_id();

        return $this->departmentRepository->create($data);
    }

    public function updateDepartment(array $data, int $id): mixed
    {
        return $this->departmentRepository->update($data, $id);
    }

    public function deleteDepartmentById(int $id): int
    {
        return $this->departmentRepository->delete($id);
    }
}
