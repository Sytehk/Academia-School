<?php

declare(strict_types=1);

namespace Modules\SystemConfiguration\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\SystemConfiguration\Models\UserLog;
use Modules\SystemConfiguration\Repositories\UserLogRepository;

class UserLogService
{
    public function __construct(
        private readonly UserLogRepository $userLogRepository
    ) {}

    public function getUserLogs(array $filter = [], int $perPage = 100): LengthAwarePaginator
    {
        return $this->userLogRepository->paginate($perPage, $filter);
    }

    public function findUserLogById(int $id): ?UserLog
    {
        return $this->userLogRepository->show((int) $id);
    }

    public function createUserLog(array $data): ?UserLog
    {
        $data['institute_id'] = get_institute_id();
        $data['branch_id'] = get_branch_id();

        return $this->userLogRepository->create($data);
    }

    public function updateUserLog(array $data, int $id): mixed
    {
        return $this->userLogRepository->update($data, $id);
    }

    public function deleteUserLogById(int $id): int
    {
        return $this->userLogRepository->delete($id);
    }
}
