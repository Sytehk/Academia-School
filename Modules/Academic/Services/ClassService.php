<?php

declare(strict_types=1);

namespace Modules\Academic\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Academic\Models\ClassModel;
use Modules\Academic\Repositories\ClassModelRepository;

class ClassService
{
    public function __construct(
        private readonly ClassModelRepository $classRepository
    ) {}

    public function getClasses(array $filter = [], int $perPage = 100): LengthAwarePaginator
    {
        return $this->classRepository->paginate($perPage, $filter);
    }

    public function findClassById(int $id): ?ClassModel
    {
        return $this->classRepository->show((int) $id);
    }

    public function createClass(array $data): ?ClassModel
    {
        $data['institute_id'] = get_institute_id();
        $data['branch_id'] = get_branch_id();

        return $this->classRepository->create($data);
    }

    public function updateClass(array $data, int $id): mixed
    {
        return $this->classRepository->update($data, $id);
    }

    public function deleteClassById(int $id): int
    {
        return $this->classRepository->delete($id);
    }
}
