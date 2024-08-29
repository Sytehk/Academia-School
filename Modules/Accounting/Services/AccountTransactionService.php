<?php

declare(strict_types=1);

namespace Modules\Accounting\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Modules\Accounting\Models\AccountTransaction;
use Modules\Accounting\Models\AccountTransactionDetail;
use Modules\Accounting\Repositories\AccountTransactionDetailRepository;
use Modules\Accounting\Repositories\AccountTransactionRepository;
use Modules\Payroll\Models\PayrollAccountingMapping;

class AccountTransactionService
{
    public function __construct(
        private readonly AccountTransactionRepository $accountTransactionRepository,
        private readonly AccountTransactionDetailRepository $accountTransactionDetailRepository,
    ) {}

    public function getAccountTransactions(array $filter = [], int $perPage = 100): LengthAwarePaginator
    {
        return $this->accountTransactionRepository->paginate($perPage, $filter);
    }

    public function findAccountTransactionById(int $id): ?AccountTransaction
    {
        return $this->accountTransactionRepository->show((int) $id);
    }

    public function createAccountTransaction(array $data): ?AccountTransaction
    {
        $data['institute_id'] = get_institute_id();
        $data['branch_id'] = get_branch_id();

        return $this->accountTransactionRepository->create($data);
    }

    public function updateAccountTransaction(array $data, int $id): mixed
    {
        return $this->accountTransactionRepository->update($data, $id);
    }

    public function deleteAccountTransactionById(int $id): int
    {
        return $this->accountTransactionRepository->delete($id);
    }

    public function createAccountTransactionDetail(array $data): ?AccountTransactionDetail
    {
        return $this->accountTransactionDetailRepository->create($data);
    }

    public function prepareForAccTransAndDetails(array $data, $amount_type, $type = null)
    {
        $ledger_fund = PayrollAccountingMapping::resolveForCurrentTenant();
        $transactionData = [
            'institute_id' => get_institute_id(),
            'branch_id' => get_branch_id(),
            'voucher_id' => (int) sprintf('%016d', rand(0, 9999999999999999)),
            'category_id' => 1,
            'fund_id' => $ledger_fund->fund_id,
            'transaction_date' => now(),
            'type' => ! is_null($type) ? $type : 'payment',
            'fund_to_id' => $data['user_id'],
            'created_by' => Auth::id(),
            $amount_type => $data['amount'],
        ];

        $transaction = $this->createAccountTransaction($transactionData);
        if (! empty($transaction)) {
            $transactionData['account_transactions_id'] = $transaction->id;
            $transactionData['ledger_id'] = $ledger_fund->ledger_id;
            $transactionData['transaction_date'] = $transaction->transaction_date;
            $this->createAccountTransactionDetail($transactionData);
        }
    }
}
