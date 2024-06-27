<?php

declare(strict_types=1);

namespace Modules\Academic\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Academic\Models\Period;
use Modules\Academic\Repositories\PeriodRepository;

class PeriodService
{
    public function __construct(
        private readonly PeriodRepository $periodRepository
    ) {}

    public function getPeriods(array $filter = [], int $perPage = 100): LengthAwarePaginator
    {
        return $this->periodRepository->paginate($perPage);
    }

    public function findPeriodById(int $id): ?Period
    {
        return $this->periodRepository->show((int) $id);
    }

    public function createPeriod(array $data): ?Period
    {
        $data['institute_id'] = get_institute_id();
        $data['branch_id'] = get_branch_id();

        return $this->periodRepository->create($data);
    }

    public function updatePeriod(array $data, int $id): mixed
    {
        return $this->periodRepository->update($data, $id);
    }

    public function deletePeriodById(int $id): int
    {
        return $this->periodRepository->delete($id);
    }
}
