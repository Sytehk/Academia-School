<?php

declare(strict_types=1);

namespace Modules\Academic\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Academic\Models\Staff;
use Modules\Academic\Repositories\StaffRepository;

class StaffService
{
    public function __construct(
        private readonly StaffRepository $staffRepository
    ) {}

    public function getStaffs(array $filter = [], int $perPage = 100): LengthAwarePaginator
    {
        return $this->staffRepository->paginate($perPage, $filter);
    }

    public function findStaffById(int $id): ?Staff
    {
        return $this->staffRepository->show($id);
    }

    public function createStaff(array $data): ?Staff
    {
        $data['institute_id'] = get_institute_id();
        $data['branch_id'] = get_branch_id();

        return $this->staffRepository->create($data);
    }

    public function updateStaff(array $data, int $id): mixed
    {
        return $this->staffRepository->update($data, $id);
    }

    public function deleteStaffById(int $id): int
    {
        return $this->staffRepository->delete($id);
    }
}
