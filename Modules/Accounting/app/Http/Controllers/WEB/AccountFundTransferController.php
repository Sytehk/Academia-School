<?php

namespace Modules\Accounting\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Models\AccountingFund;
use Modules\Accounting\Models\AccountTransaction;
use Modules\Accounting\Models\AccountTransactionDetail;
use RealRashid\SweetAlert\Facades\Alert;

class AccountFundTransferController extends Controller
{
    public function index(): View|Factory
    {
        return view('accounting::fund_transfer.index');
    }

    public function store(Request $request): Redirector|RedirectResponse
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
                    $transactionDetail->save();

                    DB::commit();

                    Alert::toast('Fund balance transfer transaction has been successfully.', 'success');

                    return redirect('account-fund-transfers');
                }
            }

            Alert::error('Error', _lang('Fund transfer within the same fund is not possible.'));

            return redirect('account-fund-transfers');
        } catch (Exception $e) {
            DB::rollBack();

            Alert::error('Error', $e->getMessage());

            return redirect('account-fund-transfers');
        }
    }

    public function getFundBalance(Request $request): JsonResponse
    {
        $fundMethodId = $request->input('fund_id');
        $accountFund = AccountingFund::where('id', $fundMethodId)->first();

        return response()->json([
            'success' => true,
            'balance' => floatval($accountFund->balance),
        ]);
    }
}
