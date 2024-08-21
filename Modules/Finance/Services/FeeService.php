<?php

declare(strict_types=1);

namespace Modules\Finance\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Finance\Models\Fee;
use Modules\Finance\Repositories\FeeRepository;

class FeeService
{
    public function __construct(
        private readonly FeeRepository $feeRepository
    ) {}

    public function getFees(array $filter = [], int $perPage = 100): LengthAwarePaginator
    {
        return $this->feeRepository->paginate($perPage, $filter);
    }

    public function findFeeById(int $id): ?Fee
    {
        return $this->feeRepository->show((int) $id);
    }

    public function createFee(array $data): ?Fee
    {
        // Update the session_id with the current session_id.
        $data['session_id'] = get_option('academic_year');
        $data['institute_id'] = get_institute_id();
        $data['branch_id'] = get_branch_id();

        return $this->feeRepository->create($data);
    }

    public function updateFee(array $data, int $id): mixed
    {
        return $this->feeRepository->update($data, $id);
    }

    public function deleteFeeById(int $id): int
    {
        return $this->feeRepository->delete($id);
    }
}
