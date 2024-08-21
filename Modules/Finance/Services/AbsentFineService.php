<?php

declare(strict_types=1);

namespace Modules\Finance\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Academic\Models\AbsentFine;
use Modules\Finance\Repositories\AbsentFineRepository;

class AbsentFineService
{
    public function __construct(
        private readonly AbsentFineRepository $feeRepository
    ) {}

    public function getAbsentFines(array $filter = [], int $perPage = 100): LengthAwarePaginator
    {
        return $this->feeRepository->paginate($perPage, $filter);
    }

    public function findAbsentFineById(int $id): ?AbsentFine
    {
        return $this->feeRepository->show((int) $id);
    }

    public function createAbsentFine(array $data): ?AbsentFine
    {
        $data['institute_id'] = get_institute_id();
        $data['branch_id'] = get_branch_id();

        return $this->feeRepository->create($data);
    }

    public function updateAbsentFine(array $data, int $id): mixed
    {
        return $this->feeRepository->update($data, $id);
    }

    public function deleteAbsentFineById(int $id): int
    {
        return $this->feeRepository->delete($id);
    }

    public function findByClassAndPeriod(int $classId, int $periodId, $excludeId = null): ?AbsentFine
    {
        $query = AbsentFine::where('class_id', $classId)
            ->where('period_id', $periodId);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId); // Exclude the current record if updating
        }

        return $query->first();
    }
}
