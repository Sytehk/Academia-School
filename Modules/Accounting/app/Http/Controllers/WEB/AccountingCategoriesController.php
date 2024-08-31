<?php

namespace Modules\Accounting\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Modules\Accounting\Http\Requests\AccountingCategoryCreateRequest;
use Modules\Accounting\Http\Requests\AccountingCategoryUpdateRequest;
use Modules\Accounting\Services\AccountingCategoryService;
use RealRashid\SweetAlert\Facades\Alert;

class AccountingCategoriesController extends Controller
{
    public function __construct(private readonly AccountingCategoryService $accountingCategoryService) {}

    public function index(): View|Factory
    {
        return view('accounting::accounting_categories.index', [
            'accountingCategories' => $this->accountingCategoryService->getAccountingCategories(),
        ]);
    }

    public function store(AccountingCategoryCreateRequest $request): Redirector|RedirectResponse
    {
        $accountingCategory = $this->accountingCategoryService->createAccountingCategory($request->validated());

        if (! $accountingCategory) {
            Alert::error('Error', _lang('Something went wrong. Accounting Category can not be submitted.'));

            return redirect('accounting-categories');
        }

        Alert::toast('Accounting Category application has been submitted.', 'success');

        return redirect('accounting-categories');
    }

    public function edit(int $id): Factory|Redirector|RedirectResponse|View
    {
        $accountingCategory = $this->accountingCategoryService->findAccountingCategoryById($id);

        if (! $accountingCategory) {
            Alert::error('Error', _lang('Something went wrong. Accounting Category can not be edit.'));

            return redirect('accounting-categories');
        }

        return view('accounting::accounting_categories.edit', [
            'accountingCategory' => $accountingCategory,
        ]);
    }

    public function update(AccountingCategoryUpdateRequest $request, int $id): Redirector|RedirectResponse
    {
        $accountingCategory = $this->accountingCategoryService->findAccountingCategoryById($id);

        if (! $accountingCategory) {
            Alert::error('Error', _lang('Something went wrong. Accounting Category can not be update.'));

            return redirect('accounting-categories');
        }

        $this->accountingCategoryService->updateAccountingCategory($request->validated(), $id);

        Alert::toast('Accounting Category application has been updated.', 'success');

        return redirect('accounting-categories');
    }

    public function destroy(int $id): Redirector|RedirectResponse
    {
        $accountingCategory = $this->accountingCategoryService->deleteAccountingCategoryById($id);

        if (! $accountingCategory) {
            Alert::error('Error', _lang('Something went wrong. Accounting Category can not be delete.'));

            return redirect('accounting-categories');
        }

        Alert::toast('Accounting Category application has been deleted.', 'success');

        return redirect('accounting-categories');
    }
}
