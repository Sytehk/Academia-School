<?php

namespace Modules\Accounting\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Modules\Accounting\Models\AccountingGroup;
use Modules\Accounting\Services\AccountingCategoryService;
use Modules\Accounting\Services\AccountingLedgerService;
use RealRashid\SweetAlert\Facades\Alert;

class AccountingLedgersController extends Controller
{
    public function __construct(
        private readonly AccountingLedgerService $accountingLedgerService,
        private readonly AccountingCategoryService $accountingCategoryService
    ) {}

    public function index(): View|Factory
    {
        return view('accounting::accounting_ledgers.index', [
            'accountingLedgers' => $this->accountingLedgerService->getAccountingLedgers(),
            'accountingCategories' => $this->accountingCategoryService->getAccountingCategories(),
        ]);
    }

    public function store(Request $request): Redirector|RedirectResponse
    {
        $validated = $request->validate([
            'ledger_name' => 'required|string|max:50',
            'accounting_category_id' => 'required|numeric',
            'accounting_group_id' => 'required|numeric',
        ]);

        $accountingLedger = $this->accountingLedgerService->createAccountingLedger($validated);

        if (! $accountingLedger) {
            Alert::error('Error', _lang('Something went wrong. Accounting Ledger can not be submitted.'));

            return redirect('accounting-ledgers');
        }

        Alert::toast('Accounting Ledger has been added.', 'success');

        return redirect('accounting-ledgers');
    }

    public function edit(int $id): Factory|Redirector|RedirectResponse|View
    {
        $accountingLedger = $this->accountingLedgerService->findAccountingLedgerById($id);

        if (! $accountingLedger) {
            Alert::error('Error', _lang('Something went wrong. Accounting Ledger can not be edit.'));

            return redirect('accounting-ledgers');
        }

        $accountingLedger->load(['accountingGroup', 'accountingCategory']);

        return view('accounting::accounting_ledgers.edit', [
            'accountingLedger' => $accountingLedger,
            'accountingCategories' => $this->accountingCategoryService->getAccountingCategories(),
            'accountingGroups' => AccountingGroup::where('institute_id', get_institute_id())
                ->where('branch_id', get_branch_id())
                ->where('accounting_category_id', $accountingLedger->accounting_category_id)
                ->orderBy('name', 'asc')
                ->pluck('name', 'id'),
        ]);
    }

    public function update(Request $request, int $id): Redirector|RedirectResponse
    {
        $validated = $request->validate([
            'ledger_name' => 'required|string|max:50',
            'accounting_category_id' => 'required|numeric',
            'accounting_group_id' => 'required|numeric',
        ]);

        $accountingLedger = $this->accountingLedgerService->findAccountingLedgerById($id);

        if (! $accountingLedger) {
            Alert::error('Error', _lang('Something went wrong. Accounting Ledger can not be update.'));

            return redirect('accounting-ledgers');
        }

        $this->accountingLedgerService->updateAccountingLedger($validated, $id);

        Alert::toast('Accounting Ledger has been update successfully.', 'success');

        return redirect('accounting-ledgers');
    }

    public function destroy(int $id): Redirector|RedirectResponse
    {
        $accountingLedger = $this->accountingLedgerService->deleteAccountingLedgerById($id);

        if (! $accountingLedger) {
            Alert::error('Error', _lang('Something went wrong. Accounting Ledger can not be delete.'));

            return redirect('accounting-ledgers');
        }

        Alert::toast('Accounting Ledger has been delete successfully.', 'success');

        return redirect('accounting-ledgers');
    }
}
