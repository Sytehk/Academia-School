<?php

declare(strict_types=1);

namespace Modules\Academic\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Academic\Models\StudentCategory;
use Modules\Academic\Repositories\StudentCategoryRepository;

class StudentCategoriesService
{
    public function __construct(
        private readonly StudentCategoryRepository $studentCategoryRepository
    ) {}

    public function getStudentCategories(array $filter = [], int $perPage = 100): LengthAwarePaginator
    {
        return $this->studentCategoryRepository->paginate($perPage, $filter);
    }

    public function findStudentCategoryById(int $id): ?StudentCategory
    {
        return $this->studentCategoryRepository->show((int) $id);
    }

    public function createStudentCategory(array $data): ?StudentCategory
    {
        $data['institute_id'] = get_institute_id();
        $data['branch_id'] = get_branch_id();

        return $this->studentCategoryRepository->create($data);
    }

    public function updateStudentCategory(array $data, int $id): mixed
    {
        return $this->studentCategoryRepository->update($data, $id);
    }

    public function deleteStudentCategoryById(int $id): int
    {
        return $this->studentCategoryRepository->delete($id);
    }
}
