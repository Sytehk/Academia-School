<?php

declare(strict_types=1);

namespace Modules\Accounting\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Accounting\Models\AccountingGroup;
use Modules\Accounting\Repositories\AccountingGroupRepository;

class AccountingGroupService
{
    public function __construct(
        private readonly AccountingGroupRepository $accountingGroupRepository
    ) {}

    public function getAccountingGroups(array $filter = [], int $perPage = 100): LengthAwarePaginator
    {
        return $this->accountingGroupRepository->paginate($perPage, $filter);
    }

    public function findAccountingGroupById(int $id): ?AccountingGroup
    {
        return $this->accountingGroupRepository->show((int) $id);
    }

    public function createAccountingGroup(array $data): ?AccountingGroup
    {
        $data['institute_id'] = get_institute_id();
        $data['branch_id'] = get_branch_id();

        return $this->accountingGroupRepository->create($data);
    }

    public function updateAccountingGroup(array $data, int $id): mixed
    {
        return $this->accountingGroupRepository->update($data, $id);
    }

    public function deleteAccountingGroupById(int $id): int
    {
        return $this->accountingGroupRepository->delete($id);
    }
}
