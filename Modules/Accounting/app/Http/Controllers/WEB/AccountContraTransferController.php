<?php

namespace Modules\Accounting\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Models\AccountingLedger;
use Modules\Accounting\Models\AccountTransaction;
use Modules\Accounting\Models\AccountTransactionDetail;
use RealRashid\SweetAlert\Facades\Alert;

class AccountContraTransferController extends Controller
{
    public function index(): View|Factory
    {
        return view('accounting::contra_transfer.index');
    }

    public function store(Request $request): Redirector|RedirectResponse
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
                    $accountTransaction->save();

                    if ($ledgerOne) {
                        $ledgerOneBalance = $ledgerOne->balance - $amount;
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
                    $transactionDetail1->save();

                    $ledgerTwo = AccountingLedger::where('id', $paymentTo)->first();
                    if ($ledgerTwo) {
                        $ledgerTwoBalance = $ledgerTwo->balance + $amount;
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
                    $transactionDetail2->save();

                    DB::commit();

                    Alert::toast(_lang('Contra balance transfer transaction has been submitted.'), 'success');

                    return redirect('account-contra-transfers');
                }

                Alert::error('Error', _lang('Insufficient balance !'));

                return redirect('account-contra-transfers');
            }

            Alert::error('Error', _lang('Contra balance transfer within the same account is not possible.'));

            return redirect('account-contra-transfers');
        } catch (\Exception $e) {
            DB::rollBack();

            Alert::error('Error', $e->getMessage());

            return back();
        }
    }
}
