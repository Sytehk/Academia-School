<?php

declare(strict_types=1);

namespace Modules\Accounting\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Accounting\Models\AccountingCategory;
use Modules\Accounting\Models\AccountingType;
use Modules\Accounting\Repositories\AccountingCategoryRepository;

class AccountingCategoryService
{
    public function __construct(
        private readonly AccountingCategoryRepository $accountingCategoryRepository
    ) {}

    public function getAccountingCategories(array $filter = [], int $perPage = 100): LengthAwarePaginator
    {
        return $this->accountingCategoryRepository->paginate($perPage, $filter);
    }

    public function findAccountingCategoryById(int $id): ?AccountingCategory
    {
        return $this->accountingCategoryRepository->show((int) $id);
    }

    public function createAccountingCategory(array $data): ?AccountingCategory
    {
        // Append nature to data
        $data['nature'] = AccountingType::getAccountingNatureByType($data['type']);
        $data['institute_id'] = get_institute_id();
        $data['branch_id'] = get_branch_id();

        return $this->accountingCategoryRepository->create($data);
    }

    public function updateAccountingCategory(array $data, int $id): mixed
    {
        return $this->accountingCategoryRepository->update($data, $id);
    }

    public function deleteAccountingCategoryById(int $id): int
    {
        return $this->accountingCategoryRepository->delete($id);
    }
}
