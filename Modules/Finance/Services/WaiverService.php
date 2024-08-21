<?php

declare(strict_types=1);

namespace Modules\Finance\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Finance\Models\Waiver;
use Modules\Finance\Repositories\WaiverRepository;

class WaiverService
{
    public function __construct(
        private readonly WaiverRepository $waiverRepository
    ) {}

    public function getWaivers(array $filter = [], int $perPage = 100): LengthAwarePaginator
    {
        return $this->waiverRepository->paginate($perPage, $filter);
    }

    public function findWaiverById(int $id): ?Waiver
    {
        return $this->waiverRepository->show((int) $id);
    }

    public function createWaiver(array $data): ?Waiver
    {
        $data['institute_id'] = get_institute_id();
        $data['branch_id'] = get_branch_id();

        return $this->waiverRepository->create($data);
    }

    public function updateWaiver(array $data, int $id): mixed
    {
        return $this->waiverRepository->update($data, $id);
    }

    public function deleteWaiverById(int $id): int
    {
        return $this->waiverRepository->delete($id);
    }
}
