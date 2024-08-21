<?php

declare(strict_types=1);

namespace Modules\Finance\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Finance\Models\FeeSubHead;
use Modules\Finance\Repositories\FeeSubHeadRepository;

class FeeSubHeadService
{
    public function __construct(
        private readonly FeeSubHeadRepository $feeSubHeadRepository
    ) {}

    public function getFeeSubHeads(array $filter = [], int $perPage = 100): LengthAwarePaginator
    {
        return $this->feeSubHeadRepository->paginate($perPage, $filter);
    }

    public function findFeeSubHeadById(int $id): ?FeeSubHead
    {
        return $this->feeSubHeadRepository->show((int) $id);
    }

    public function createFeeSubHead(array $data): ?FeeSubHead
    {
        $data['institute_id'] = get_institute_id();
        $data['branch_id'] = get_branch_id();

        return $this->feeSubHeadRepository->create($data);
    }

    public function updateFeeSubHead(array $data, int $id): mixed
    {
        return $this->feeSubHeadRepository->update($data, $id);
    }

    public function deleteFeeSubHeadById(int $id): int
    {
        return $this->feeSubHeadRepository->delete($id);
    }
}
