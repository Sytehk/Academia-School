<?php

namespace Modules\Accounting\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Accounting\Models\AccountingFund;
use Modules\Accounting\Models\AccountingLedger;
use Modules\Accounting\Models\AccountTransaction;
use Modules\Accounting\Models\AccountTransactionDetail;
use RealRashid\SweetAlert\Facades\Alert;

class AccountJournalTransactionsController extends Controller
{
    public function index(): View|Factory
    {
        return view('accounting::journal_transactions.index');
    }

    public function store(Request $request): Redirector|RedirectResponse
    {
        try {
            DB::beginTransaction();

            $fundId = $request->fund_id;
            $cashLedgerId = $request->cash_ledger_id;
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
                            throw ValidationException::withMessages([
                                'error' => [_lang('Insufficient Balance.')],
                            ]);
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
                    $accountTransaction1->save();

                    $transactionDetail2 = new AccountTransactionDetail;
                    $transactionDetail2->fund_id = $fundId;
                    $transactionDetail2->ledger_id = $cashLedgerAccount->id;
                    $transactionDetail2->account_transactions_id = $accountTransaction1->id;
                    $transactionDetail2->transaction_date = $transactionDate;
                    $transactionDetail2->debit = $sumOfDebits;
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
                    $accountTransaction1->save();

                    $transactionDetail2 = new AccountTransactionDetail;
                    $transactionDetail2->fund_id = $fundId;
                    $transactionDetail2->ledger_id = $cashLedgerAccount->id;
                    $transactionDetail2->account_transactions_id = $accountTransaction1->id;
                    $transactionDetail2->transaction_date = $transactionDate;
                    $transactionDetail2->credit = $sumOfCredits;
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

                        $transactionDetail->save();
                    }
                }

                DB::commit();

                Alert::toast('Journal Payment transaction has been submitted.', 'success');

                return redirect('journal-transactions');
            }

            Alert::error('Error', _lang('Journal transaction wil be not equal with cash amount.'));

            return redirect('journal-transactions');
        } catch (Exception $e) {
            DB::rollBack();

            Alert::error('Error', $e->getMessage());

            return redirect('journal-transactions');
        }
    }

    public function report(Request $request): Factory|View
    {
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $transactions = AccountTransaction::with('transactionDetails.accountingLedger')->get();
        if ($transactions && count($transactions) > 0) {
            return view('accounting::accounting-report.transaction.account-journal', compact('fromDate', 'toDate', 'transactions'));
        }

        return view('accounting::journal_transactions.report');
    }
}
