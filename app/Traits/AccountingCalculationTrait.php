<?php

namespace App\Traits;

use App\Exceptions\AccountingTransactionException;
use Exception;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Models\AccountingFund;
use Modules\Accounting\Models\AccountingLedger;
use Modules\Accounting\Models\AccountTransaction;
use Modules\Accounting\Models\AccountTransactionDetail;

trait AccountingCalculationTrait
{
    public function getAccountingTransactionSummaryByDate($filter = [])
    {
        $startDate = $filter['start_date'] ?? date('Y-m-d');
        $endDate = $filter['end_date'] ?? date('Y-m-d');

        $query = AccountTransactionDetail::select([
            'account_transaction_details.ledger_id',
            'accounting_ledgers.ledger_name',
            DB::raw('SUM(account_transaction_details.debit) AS total_debit'),
            DB::raw('SUM(account_transaction_details.credit) AS total_credit'),
        ])
            ->join('accounting_ledgers', 'account_transaction_details.ledger_id', '=', 'accounting_ledgers.id')
            ->whereBetween('account_transaction_details.transaction_date', [$startDate, $endDate])
            ->groupBy('account_transaction_details.ledger_id', 'accounting_ledgers.ledger_name');

        return $query->get();
    }

    public function getAccountingOpeningBalance($date)
    {
        $startDate = '2023-01-01';
        $endDate = $date;

        $summary = $this->getAccountingTransactionSummaryByDate([
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        $openingBalance = 0;
        foreach ($summary as $value) {
            $openingBalance += $value->total_debit - $value->total_credit;
        }

        return $openingBalance;
    }

    public function createAccountingTransaction($data)
    {
        try {
            DB::beginTransaction();

            $transaction = $this->createAccountTransaction($data);
            $amount = 0;
            foreach ($data['details'] as $detail) {
                $this->createAccountTransactionDetail($transaction->id, $detail);
                $amount += $detail['debit'] - $detail['credit'];
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function generateVoucherId()
    {
        // Generate a 16 digit random number.
        // Also check if the voucher id is already exists in the database.
        do {
            $voucherId = mt_rand(1000000000000000, 9999999999999999);
            $voucher = AccountTransaction::where('voucher_id', $voucherId)->first();
        } while (! empty($voucher));

        return $voucherId;
    }

    public function createAccountTransaction($data)
    {
        // Check if type is in the getAccountingTypes() array.
        if (! array_key_exists($data['type'], get_accounting_types())) {
            throw new AccountingTransactionException('Invalid transaction type.');
        }

        $transaction = new AccountTransaction;
        $transaction->institute_id = get_institute_id();
        $transaction->branch_id = get_branch_id();
        $transaction->category_id = (int) $data['category_id'];
        $transaction->type = $data['type'];
        $transaction->transaction_date = $data['transaction_date'];
        $transaction->voucher_id = $data['voucher_id'] ?? $this->generateVoucherId();
        $transaction->fund_id = $data['fund_id'] ?? null;
        $transaction->fund_to_id = $data['fund_to_id'] ?? null;
        $transaction->payment_method_id = $data['payment_method_id'] ?? null;
        $transaction->payment_method_to_id = $data['payment_method_to_id'] ?? null;
        $transaction->reference = $data['reference'] ?? null;
        $transaction->description = $data['description'] ?? null;
        $transaction->created_by = auth()->user()->id;
        $transaction->save();

        return $transaction;
    }

    public function createAccountTransactionDetail($transactionId, $data)
    {
        $detail = new AccountTransactionDetail;
        $detail->institute_id = get_institute_id();
        $detail->branch_id = get_branch_id();
        $detail->account_transactions_id = $transactionId;
        $detail->fund_id = $data['fund_id'] ?? null;
        $detail->fund_to_id = $data['fund_to_id'] ?? null;
        $detail->ledger_id = $data['ledger_id'] ?? null;
        $detail->payment_method_id = $data['payment_method_id'] ?? null;
        $detail->payment_method_to_id = $data['payment_method_to_id'] ?? null;
        $detail->transaction_date = $data['transaction_date'];
        $detail->debit = floatval($data['debit']);
        $detail->credit = floatval($data['credit']);
        $detail->save();

        // $receiptTypeLedger = AccountingLedger::where('id', $data['ledger_id'])->first();
        // if ($receiptTypeLedger) {
        //     $receiptTypeLedgerNewBalance = $receiptTypeLedger->balance + $detail->debit;
        //     $receiptTypeLedger->update([
        //         'balance' => $receiptTypeLedgerNewBalance,
        //     ]);
        // }

        // $fund = AccountingFund::where('id', 1)->first();
        // if ($fund) {
        //     $fundBalance = $fund->balance + $detail->debit;
        //     $fund->update([
        //         'balance' => $fundBalance,
        //     ]);
        // }

        // $ledgerTwo = AccountingLedger::where('id', $detail['payment_method_id'])->first();
        // if ($ledgerTwo) {
        //     $ledgerBalance = $ledgerTwo->balance + $detail->debit;
        //     $ledgerTwo->update([
        //         'balance' => $ledgerBalance,
        //     ]);
        // }

        return $detail;
    }
}
