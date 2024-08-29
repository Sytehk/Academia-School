<?php

declare(strict_types=1);

namespace Modules\Accounting\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Accounting\Models\AccountingFund;
use Modules\Accounting\Repositories\AccountingFundRepository;

class AccountingFundService
{
    public function __construct(
        private readonly AccountingFundRepository $accountingFundRepository
    ) {}

    public function getAccountingFunds(array $filter = [], int $perPage = 100): LengthAwarePaginator
    {
        return $this->accountingFundRepository->paginate($perPage, $filter);
    }

    public function findAccountingFundById(int $id): ?AccountingFund
    {
        return $this->accountingFundRepository->show((int) $id);
    }

    public function createAccountingFund(array $data): ?AccountingFund
    {
        $data['institute_id'] = get_institute_id();
        $data['branch_id'] = get_branch_id();

        return $this->accountingFundRepository->create($data);
    }

    public function updateAccountingFund(array $data, int $id): mixed
    {
        return $this->accountingFundRepository->update($data, $id);
    }

    public function deleteAccountingFundById(int $id): int
    {
        return $this->accountingFundRepository->delete($id);
    }
}
