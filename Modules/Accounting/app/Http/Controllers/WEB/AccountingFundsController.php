<?php

namespace Modules\Accounting\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Modules\Accounting\Http\Requests\AccountingFundCreateRequest;
use Modules\Accounting\Services\AccountingFundService;
use RealRashid\SweetAlert\Facades\Alert;

class AccountingFundsController extends Controller
{
    public function __construct(private readonly AccountingFundService $accountingFundService) {}

    public function index(): View|Factory
    {
        return view('accounting::accounting_funds.index', [
            'accountingFunds' => $this->accountingFundService->getAccountingFunds(),
        ]);
    }

    public function store(AccountingFundCreateRequest $request): Redirector|RedirectResponse
    {
        $accountingFund = $this->accountingFundService->createAccountingFund($request->validated());

        if (! $accountingFund) {
            Alert::error('Error', _lang('Something went wrong. Accounting Fund can not be create.'));

            return redirect('accounting-funds');
        }

        Alert::toast('Accounting Fund application has been create.', 'success');

        return redirect('accounting-funds');
    }

    public function edit(int $id): Factory|Redirector|RedirectResponse|View
    {
        $accountingFund = $this->accountingFundService->findAccountingFundById($id);

        if (! $accountingFund) {
            Alert::error('Error', _lang('Something went wrong. Accounting Fund can not be edit.'));

            return redirect('accounting-funds');
        }

        return view('accounting::accounting_funds.edit', [
            'accountingFund' => $accountingFund,
        ]);
    }

    public function update(AccountingFundCreateRequest $request, int $id): Redirector|RedirectResponse
    {
        $accountingFund = $this->accountingFundService->findAccountingFundById($id);

        if (! $accountingFund) {
            Alert::error('Error', _lang('Something went wrong. Accounting Fund can not be update.'));

            return redirect('accounting-funds');
        }

        $this->accountingFundService->updateAccountingFund($request->validated(), $id);

        Alert::toast('Accounting Fund application has been updated.', 'success');

        return redirect('accounting-funds');
    }

    public function destroy(int $id): Redirector|RedirectResponse
    {
        $accountingFund = $this->accountingFundService->deleteAccountingFundById($id);

        if (! $accountingFund) {

            Alert::error('Error', _lang('Something went wrong. Accounting Fund can not be delete.'));

            return redirect('accounting-funds');
        }

        Alert::toast('Accounting Fund application has been deleted.', 'success');

        return redirect('accounting-funds');
    }
}
