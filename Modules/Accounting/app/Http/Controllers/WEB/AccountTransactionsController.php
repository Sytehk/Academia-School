<?php

namespace Modules\Accounting\Http\Controllers\WEB;

use App\Enums\VoucherType;
use App\Http\Controllers\Controller;
use App\Traits\AccountingCalculationTrait;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Http\Requests\AccountTransactionCreateRequest;
use Modules\Accounting\Models\AccountingFund;
use Modules\Accounting\Models\AccountingLedger;
use Modules\Accounting\Models\AccountTransaction;
use Modules\Accounting\Models\AccountTransactionDetail;
use RealRashid\SweetAlert\Facades\Alert;

class AccountTransactionsController extends Controller
{
    use AccountingCalculationTrait;

    public function index(): View|Factory
    {
        return view('accounting::account_transactions.index', [
            'type' => request()->type ?? VoucherType::PAYMENT,
        ]);
    }

    public function store(AccountTransactionCreateRequest $request)
    {
        $ledgerIds = $request->ledger_ids ?? [];
        $amounts = $request->amounts;
        $totalAmount = array_sum($amounts);
        $paymentMethodId = $request->payment_method_id;
        $type = null;
        $paymentTypeLedger = AccountingLedger::where('id', $paymentMethodId)->first();

        if ($totalAmount > $paymentTypeLedger->balance && $request->type == 'payment') {
            Alert::error('Error', _lang('Insufficient balance !'));

            return redirect('account-transaction-payment');
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
                Alert::toast('Account transaction payment has been submitted.', 'success');

                return redirect('account-transaction-payment');
            }
            if ($type === 'receipt') {
                Alert::toast('Account transaction receipt has been submitted.', 'success');

                return redirect('account-transaction-payment?type=receipt');
            }
        } catch (\Exception $e) {
            DB::rollBack();

            Alert::error('Error', $e->getMessage());

            return redirect('account-transaction-payment');
        }
    }

    public function getLedgerAccountBalance(Request $request): JsonResponse
    {
        $paymentMethodId = $request->input('payment_method_id');
        $accountLedger = AccountingLedger::where('id', $paymentMethodId)->first();

        return response()->json([
            'success' => true,
            'balance' => $accountLedger->balance,
        ]);
    }
}
