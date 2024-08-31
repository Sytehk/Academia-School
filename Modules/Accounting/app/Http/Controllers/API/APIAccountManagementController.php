<?php

namespace Modules\Accounting\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\AccountingCalculationTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Http\Requests\AccountTransactionCreateRequest;
use Modules\Accounting\Models\AccountingCategory;
use Modules\Accounting\Models\AccountingFund;
use Modules\Accounting\Models\AccountingLedger;
use Modules\Accounting\Models\AccountTransaction;
use Modules\Accounting\Models\AccountTransactionDetail;

class APIAccountManagementController extends Controller
{
    use AccountingCalculationTrait;

    public function getLedgerAccountBalance(Request $request): JsonResponse
    {
        $ledgerId = (int) $request->ledger_id;
        $accountLedger = AccountingLedger::where('id', $ledgerId)->first();
        $balance = floatval($accountLedger->balance ?? 0);

        return response()->json([
            'success' => true,
            'message' => _lang('Ledger Balance.'),
            'balance' => $balance,
        ]);
    }

    public function cartOfAccounts(Request $request): JsonResponse
    {
        $perPage = (int) $request->per_page ?: 10;
        try {
            $chartOfAccounts = AccountingCategory::with('accountingGroups')->select('id', 'name', 'nature', 'type')->paginate($perPage);

            return $this->responseSuccess(
                $chartOfAccounts,
                _lang('Chart Of Accounts has been fetched successfully.')
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function accountTransaction(AccountTransactionCreateRequest $request): JsonResponse
    {
        $ledgerIds = $request->ledger_ids ?? [];
        $amounts = $request->amounts;
        $totalAmount = array_sum($amounts);
        $paymentMethodId = $request->payment_method_id;
        $type = null;
        $paymentTypeLedger = AccountingLedger::where('id', $paymentMethodId)->first();

        if ($totalAmount > $paymentTypeLedger->balance && $request->type == 'payment') {
            return $this->responseError([], _lang('Insufficient balance !'), 400);
        }

        try {
            DB::beginTransaction();
            $categoryId = AccountingLedger::where('id', $paymentMethodId)->first()->accounting_category_id;

            $accountTransaction = new AccountTransaction;
            $accountTransaction->voucher_id = $this->generateVoucherId();
            $accountTransaction->fund_id = $request->fund_id;
            $accountTransaction->transaction_date = $request->transaction_date;
            $accountTransaction->payment_method_id = $paymentMethodId;
            $accountTransaction->category_id = $categoryId;
            $accountTransaction->type = $request->type;
            $accountTransaction->reference = $request->reference;
            $accountTransaction->description = $request->description;
            $accountTransaction->created_by = Auth::id();
            $accountTransaction->institute_id = get_institute_id();
            $accountTransaction->branch_id = get_branch_id();
            $accountTransaction->save();

            $totalAmount = 0;
            foreach ($ledgerIds as $key => $ledgerId) {
                $amount = (float) $amounts[$key];
                $totalAmount += $amount;

                $type = $request->type;
                $debit = ($type === 'payment') ? $amount : 0;
                $credit = ($type === 'receipt') ? $amount : 0;

                $transactionDetail = new AccountTransactionDetail;
                $transactionDetail->account_transactions_id = $accountTransaction->id;
                $transactionDetail->ledger_id = $ledgerId;
                $transactionDetail->fund_id = $request->fund_id;
                $transactionDetail->payment_method_id = $paymentMethodId;
                $transactionDetail->transaction_date = $request->transaction_date;
                $transactionDetail->debit = $debit;
                $transactionDetail->credit = $credit;
                $transactionDetail->institute_id = get_institute_id();
                $transactionDetail->branch_id = get_branch_id();
                $transactionDetail->save();

                if ($type === 'payment') {
                    if ($paymentTypeLedger) {
                        $paymentTypeLedgerNewBalance = $paymentTypeLedger->balance - $amount;
                        $paymentTypeLedger->balance = $paymentTypeLedgerNewBalance;
                        $paymentTypeLedger->save();
                    }

                    $ledgerOne = AccountingLedger::where('id', $ledgerId)->first();
                    if ($ledgerOne) {
                        $ledgerOneNewBalance = $ledgerOne->balance - $amount;
                        $ledgerOne->balance = $ledgerOneNewBalance;
                        $ledgerOne->save();
                    }

                    $fundOne = AccountingFund::where('id', $request->fund_id)->first();
                    if ($fundOne) {
                        $newBalance = $fundOne->balance - $amount;
                        $fundOne->balance = $newBalance;
                        $fundOne->save();
                    }
                }

                if ($type === 'receipt') {
                    $receiptTypeLedger = AccountingLedger::where('id', $paymentMethodId)->first();
                    if ($receiptTypeLedger) {
                        $receiptTypeLedgerNewBalance = $receiptTypeLedger->balance + $amount;
                        $receiptTypeLedger->balance = $receiptTypeLedgerNewBalance;
                        $receiptTypeLedger->save();
                    }

                    $fundTwo = AccountingFund::where('id', $request->fund_id)->first();
                    if ($fundTwo) {
                        $newBalance = $fundTwo->balance + $amount;
                        $fundTwo->balance = $newBalance;
                        $fundTwo->save();
                    }

                    $ledgerTwo = AccountingLedger::where('id', $ledgerId)->first();
                    if ($ledgerTwo) {
                        $newBalance = $ledgerTwo->balance + $amount;
                        $ledgerTwo->balance = $newBalance;
                        $ledgerTwo->save();
                    }
                }
            }

            DB::commit();
            if ($type === 'payment') {
                return $this->responseSuccess([], _lang('Account Payment transaction has been submitted.'));
            }
            if ($type === 'receipt') {
                return $this->responseSuccess([], _lang('Account Receipt transaction has been submitted.'));
            }
        } catch (Exception $e) {
            DB::rollback();

            return $this->responseError([], $e->getMessage());
        }

        return $this->responseError([], _lang('Something went wrong!'), 422);
    }

    public function accountContraTransfer(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $amount = floatval($request->amount);
            $paymentFrom = intval($request->payment_method_id);
            $paymentTo = intval($request->payment_method_to_id);

            if ($paymentFrom != $paymentTo) {
                $ledgerOne = AccountingLedger::where('id', $paymentFrom)->first();
                $ledgerOneBalance = $ledgerOne->balance;

                if ($ledgerOneBalance >= $amount) {
                    $accountTransaction = new AccountTransaction;
                    $accountTransaction->voucher_id = (int) sprintf('%016d', rand(0, 9999999999999999));
                    $accountTransaction->category_id = $paymentFrom;
                    $accountTransaction->fund_id = 1;
                    $accountTransaction->payment_method_id = $paymentFrom;
                    $accountTransaction->payment_method_to_id = $paymentTo;
                    $accountTransaction->transaction_date = $request->transaction_date;
                    $accountTransaction->type = $request->type;
                    $accountTransaction->reference = $request->reference;
                    $accountTransaction->description = $request->description;
                    $accountTransaction->created_by = Auth::id();
                    $accountTransaction->institute_id = get_institute_id();
                    $accountTransaction->branch_id = get_branch_id();
                    $accountTransaction->save();

                    if ($ledgerOne) {
                        $ledgerOneBalance = floatval($ledgerOne->balance) - $amount;
                        $ledgerOne->balance = $ledgerOneBalance;
                        $ledgerOne->save();
                    }

                    $transactionDetail1 = new AccountTransactionDetail;
                    $transactionDetail1->ledger_id = $ledgerOne->id;
                    $transactionDetail1->payment_method_id = $paymentFrom;
                    $transactionDetail1->payment_method_to_id = $paymentTo;
                    $transactionDetail1->account_transactions_id = $accountTransaction->id;
                    $transactionDetail1->transaction_date = $request->transaction_date;
                    $transactionDetail1->debit = $amount;
                    $transactionDetail1->institute_id = get_institute_id();
                    $transactionDetail1->branch_id = get_branch_id();
                    $transactionDetail1->save();

                    $ledgerTwo = AccountingLedger::where('id', $paymentTo)->first();
                    if ($ledgerTwo) {
                        $ledgerTwoBalance = floatval($ledgerTwo->balance) + $amount;
                        $ledgerTwo->balance = $ledgerTwoBalance;
                        $ledgerTwo->save();
                    }

                    $transactionDetail2 = new AccountTransactionDetail;
                    $transactionDetail2->ledger_id = $ledgerTwo->id;
                    $transactionDetail2->payment_method_id = $paymentFrom;
                    $transactionDetail2->payment_method_to_id = $paymentTo;
                    $transactionDetail2->account_transactions_id = $accountTransaction->id;
                    $transactionDetail2->transaction_date = $request->transaction_date;
                    $transactionDetail2->credit = $amount;
                    $transactionDetail2->institute_id = get_institute_id();
                    $transactionDetail2->branch_id = get_branch_id();
                    $transactionDetail2->save();

                    DB::commit();

                    return $this->responseSuccess([], _lang('Contra balance transfer transaction has been submitted.'));
                }

                return $this->responseError([], _lang('Insufficient balance !'), 400);
            }

            return $this->responseError([], _lang('Contra balance transfer within the same account is not possible.'), 422);
        } catch (Exception $e) {
            DB::rollback();

            return $this->responseError([], $e->getMessage());
        }
    }

    public function accountJournalTransfer(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $fundId = intval($request->fund_id);
            $cashLedgerId = intval($request->cash_ledger_id);
            $cashDebit = floatval($request->cash_debit);
            $cashCredit = floatval($request->cash_credit);
            $debits = $request->debits;
            $credits = $request->credits;
            $transactionDate = $request->transaction_date;
            $type = $request->type;

            $sumOfDebits = floatval(array_sum($debits));
            $sumOfCredits = floatval(array_sum($credits));
            $fund = AccountingFund::findOrFail($fundId);
            $cashLedgerAccount = AccountingLedger::findOrFail($cashLedgerId);
            $cashLedgerAccountBalance = floatval($cashLedgerAccount->balance);

            if ($cashCredit > 0 && $cashCredit === $sumOfDebits || $cashDebit > 0 && $cashDebit === $sumOfCredits) {
                if ($cashCredit > 0 && $cashCredit === $sumOfDebits) {
                    // Check for insufficient balance
                    if ($cashLedgerAccount && $cashCredit) {
                        if ($cashLedgerAccountBalance < $cashCredit || $fund->balance < $cashCredit) {
                            return $this->responseError([], _lang('Insufficient Balance.'), 400);
                        }
                    }

                    $accountTransaction1 = new AccountTransaction;
                    $accountTransaction1->fund_id = $fundId;
                    $accountTransaction1->voucher_id = (int) sprintf('%016d', rand(0, 9999999999999999));
                    $accountTransaction1->transaction_date = $transactionDate;
                    $accountTransaction1->type = $type;
                    $accountTransaction1->reference = $request->reference;
                    $accountTransaction1->description = $request->description;
                    $accountTransaction1->created_by = Auth::id();
                    $accountTransaction1->institute_id = get_institute_id();
                    $accountTransaction1->branch_id = get_branch_id();
                    $accountTransaction1->save();

                    $transactionDetail2 = new AccountTransactionDetail;
                    $transactionDetail2->fund_id = $fundId;
                    $transactionDetail2->ledger_id = $cashLedgerAccount->id;
                    $transactionDetail2->account_transactions_id = $accountTransaction1->id;
                    $transactionDetail2->transaction_date = $transactionDate;
                    $transactionDetail2->debit = $sumOfDebits;
                    $transactionDetail2->institute_id = get_institute_id();
                    $transactionDetail2->branch_id = get_branch_id();
                    $transactionDetail2->save();

                    $cashLedgerAccount->update(['balance' => $cashLedgerAccountBalance - $sumOfDebits]);
                    $fund->update(['balance' => $fund->balance - $sumOfDebits]);

                    // Retrieve ledger account and fund
                    $leaderIds = $request->ledger_ids;
                    foreach ($leaderIds as $key => $ledgerId) {
                        // Retrieve debit and credit amounts
                        $debit = floatval($request->debits[$key]);
                        $ledgerAccount = AccountingLedger::findOrFail($ledgerId);
                        $ledgerBalance = $ledgerAccount->balance + $debit;
                        $ledgerAccount->update(['balance' => $ledgerBalance]);

                        // Create transaction detail
                        $transactionDetail = new AccountTransactionDetail;
                        $transactionDetail->fund_id = $fundId;
                        $transactionDetail->ledger_id = $ledgerId;
                        $transactionDetail->account_transactions_id = $accountTransaction1->id;
                        $transactionDetail->transaction_date = $transactionDate;
                        $transactionDetail->credit = $debit;
                        $transactionDetail->institute_id = get_institute_id();
                        $transactionDetail->branch_id = get_branch_id();
                        $transactionDetail->save();
                    }
                }

                if ($cashDebit > 0 && $cashDebit === $sumOfCredits) {
                    $accountTransaction1 = new AccountTransaction;
                    $accountTransaction1->fund_id = $fundId;
                    $accountTransaction1->voucher_id = (int) sprintf('%016d', rand(0, 9999999999999999));
                    $accountTransaction1->transaction_date = $transactionDate;
                    $accountTransaction1->type = $type;
                    $accountTransaction1->reference = $request->reference;
                    $accountTransaction1->description = $request->description;
                    $accountTransaction1->created_by = Auth::id();
                    $accountTransaction1->institute_id = get_institute_id();
                    $accountTransaction1->branch_id = get_branch_id();
                    $accountTransaction1->save();

                    $transactionDetail2 = new AccountTransactionDetail;
                    $transactionDetail2->fund_id = $fundId;
                    $transactionDetail2->ledger_id = $cashLedgerAccount->id;
                    $transactionDetail2->account_transactions_id = $accountTransaction1->id;
                    $transactionDetail2->transaction_date = $transactionDate;
                    $transactionDetail2->credit = $sumOfCredits;
                    $transactionDetail2->institute_id = get_institute_id();
                    $transactionDetail2->branch_id = get_branch_id();
                    $transactionDetail2->save();

                    $cashLedgerAccount->update(['balance' => $cashLedgerAccountBalance + $sumOfCredits]);
                    $fund->update(['balance' => $fund->balance + $sumOfCredits]);

                    // Retrieve ledger account and fund
                    $leaderIds = $request->ledger_ids;
                    foreach ($leaderIds as $key => $ledgerId) {
                        $credit = floatval($request->credits[$key]);

                        // Update ledger account balance
                        $ledgerAccount = AccountingLedger::findOrFail($ledgerId);
                        $ledgerBalance = $ledgerAccount->balance - $credit;
                        $ledgerAccount->update(['balance' => $ledgerBalance]);

                        // Create transaction detail
                        $transactionDetail = new AccountTransactionDetail;
                        $transactionDetail->fund_id = $fundId;
                        $transactionDetail->ledger_id = $ledgerId;
                        $transactionDetail->account_transactions_id = $accountTransaction1->id;
                        $transactionDetail->transaction_date = $transactionDate;
                        $transactionDetail->debit = $credit;
                        $transactionDetail->institute_id = get_institute_id();
                        $transactionDetail->branch_id = get_branch_id();
                        $transactionDetail->save();
                    }
                }

                DB::commit();

                return $this->responseSuccess([], _lang('Journal Payment transaction has been submitted'));
            }

            return $this->responseError([], _lang('Journal transaction wil be not equal with cash amount.'), 422);
        } catch (Exception $e) {
            DB::rollBack();

            return $this->responseError([], $e->getMessage());
        }
    }

    public function accountFundTransfer(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $amount = $request->amount;
            if ($request->fund_id !== $request->fund_to_id) {
                $fundOne = AccountingFund::where('id', $request->fund_id)->first();
                $fundBalance = $fundOne->balance;

                if ($fundBalance >= $amount) {
                    $accountTransaction = new AccountTransaction;
                    $accountTransaction->voucher_id = (int) sprintf('%016d', rand(0, 9999999999999999));
                    $accountTransaction->category_id = 1;
                    $accountTransaction->fund_id = $request->fund_id;
                    $accountTransaction->fund_to_id = $request->fund_to_id;
                    $accountTransaction->transaction_date = $request->transaction_date;
                    $accountTransaction->type = $request->type;
                    $accountTransaction->reference = $request->reference;
                    $accountTransaction->description = $request->description;
                    $accountTransaction->created_by = Auth::id();
                    $accountTransaction->institute_id = get_institute_id();
                    $accountTransaction->branch_id = get_branch_id();
                    $accountTransaction->save();

                    if ($fundOne) {
                        $newBalance = $fundOne->balance - $amount;
                        $fundOne->balance = $newBalance;
                        $fundOne->save();
                    }

                    $transactionDetail = new AccountTransactionDetail;
                    $transactionDetail->ledger_id = 1;
                    $transactionDetail->account_transactions_id = $accountTransaction->id;
                    $transactionDetail->fund_id = $request->fund_id;
                    $transactionDetail->fund_to_id = $request->fund_to_id;
                    $transactionDetail->transaction_date = $request->transaction_date;
                    $transactionDetail->debit = $amount;
                    $transactionDetail->institute_id = get_institute_id();
                    $transactionDetail->branch_id = get_branch_id();
                    $transactionDetail->save();

                    $fundTwo = AccountingFund::where('id', $request->fund_to_id)->first();
                    if ($fundTwo) {
                        $newBalance = $fundTwo->balance + $amount;
                        $fundTwo->balance = $newBalance;
                        $fundTwo->save();
                    }
                    $transactionDetail = new AccountTransactionDetail;
                    $transactionDetail->ledger_id = 1;
                    $transactionDetail->account_transactions_id = $accountTransaction->id;
                    $transactionDetail->fund_id = $request->fund_to_id;
                    $transactionDetail->fund_to_id = $request->fund_id;
                    $transactionDetail->transaction_date = $request->transaction_date;
                    $transactionDetail->credit = $amount;
                    $transactionDetail->institute_id = get_institute_id();
                    $transactionDetail->branch_id = get_branch_id();
                    $transactionDetail->save();

                    DB::commit();

                    return $this->responseSuccess([], _lang('Fund balance transfer transaction has been successfully.'));
                }
            }

            return $this->responseError([], _lang('Fund transfer within the same fund is not possible.'), 422);
        } catch (Exception $e) {
            DB::rollback();

            return $this->responseError([], $e->getMessage());
        }
    }
}
