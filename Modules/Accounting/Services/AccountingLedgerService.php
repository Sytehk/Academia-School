<?php

declare(strict_types=1);

namespace Modules\Accounting\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Accounting\Models\AccountingLedger;
use Modules\Accounting\Repositories\AccountingLedgerRepository;

class AccountingLedgerService
{
    public function __construct(
        private readonly AccountingLedgerRepository $accountingLedgerRepository
    ) {}

    public function getAccountingLedgers(array $filter = [], int $perPage = 100): LengthAwarePaginator
    {
        return $this->accountingLedgerRepository->paginate($perPage, $filter);
    }

    public function findAccountingLedgerById(int $id): ?AccountingLedger
    {
        return $this->accountingLedgerRepository->show((int) $id);
    }

    public function createAccountingLedger(array $data): ?AccountingLedger
    {
        $data['institute_id'] = get_institute_id();
        $data['branch_id'] = get_branch_id();

        return $this->accountingLedgerRepository->create($data);
    }

    public function updateAccountingLedger(array $data, int $id): mixed
    {
        return $this->accountingLedgerRepository->update($data, $id);
    }

    public function deleteAccountingLedgerById(int $id): int
    {
        return $this->accountingLedgerRepository->delete($id);
    }
}
