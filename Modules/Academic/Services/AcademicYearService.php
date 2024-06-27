<?php

declare(strict_types=1);

namespace Modules\Academic\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Academic\Models\AcademicYear;
use Modules\Academic\Repositories\AcademicYearRepository;

class AcademicYearService
{
    public function __construct(
        private readonly AcademicYearRepository $academicYearRepository
    ) {}

    public function getAcademicYears(array $filter = [], int $perPage = 100): LengthAwarePaginator
    {
        return $this->academicYearRepository->paginate($perPage, $filter);
    }

    public function findAcademicYearById(int $id): ?AcademicYear
    {
        return $this->academicYearRepository->show((int) $id);
    }

    public function createAcademicYear(array $data): ?AcademicYear
    {
        return $this->academicYearRepository->create($data);
    }

    public function updateAcademicYear(array $data, int $id): mixed
    {
        return $this->academicYearRepository->update($data, $id);
    }

    public function deleteAcademicYearById(int $id): int
    {
        return $this->academicYearRepository->delete($id);
    }
}
