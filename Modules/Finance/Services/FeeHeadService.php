<?php

declare(strict_types=1);

namespace Modules\Finance\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Finance\Models\FeeHead;
use Modules\Finance\Repositories\FeeHeadRepository;

class FeeHeadService
{
    public function __construct(
        private readonly FeeHeadRepository $feeHeadRepository
    ) {}

    public function getFeeHeads(array $filter = [], int $perPage = 100): LengthAwarePaginator
    {
        return $this->feeHeadRepository->paginate($perPage, $filter);
    }

    public function findFeeHeadById(int $id): ?FeeHead
    {
        return $this->feeHeadRepository->show((int) $id);
    }

    public function createFeeHead(array $data): ?FeeHead
    {
        $data['institute_id'] = get_institute_id();
        $data['branch_id'] = get_branch_id();

        return $this->feeHeadRepository->create($data);
    }

    public function updateFeeHead(array $data, int $id): mixed
    {
        return $this->feeHeadRepository->update($data, $id);
    }

    public function deleteFeeHeadById(int $id): int
    {
        return $this->feeHeadRepository->delete($id);
    }
}
