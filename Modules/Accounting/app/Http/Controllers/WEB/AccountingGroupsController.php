<?php

namespace Modules\Accounting\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Modules\Accounting\Http\Requests\AccountingGroupCreateRequest;
use Modules\Accounting\Http\Requests\AccountingGroupUpdateRequest;
use Modules\Accounting\Models\AccountingGroup;
use Modules\Accounting\Services\AccountingGroupService;
use RealRashid\SweetAlert\Facades\Alert;

class AccountingGroupsController extends Controller
{
    public function __construct(private readonly AccountingGroupService $accountingGroupService) {}

    public function index(): View|Factory
    {
        return view('accounting::accounting_groups.index', [
            'accountingGroups' => $this->accountingGroupService->getAccountingGroups(),
        ]);
    }

    public function store(AccountingGroupCreateRequest $request): Redirector|RedirectResponse
    {
        $accountingGroup = $this->accountingGroupService->createAccountingGroup($request->validated());

        if (! $accountingGroup) {
            Alert::error('Error', _lang('Something went wrong. Accounting Group can not be submitted.'));

            return redirect('accounting-groups');
        }

        Alert::toast('Accounting Group has been submitted.', 'success');

        return redirect('accounting-groups');
    }

    public function edit(int $id): Factory|Redirector|RedirectResponse|View
    {
        $accountingGroup = $this->accountingGroupService->findAccountingGroupById($id);

        if (! $accountingGroup) {
            Alert::error('Error', _lang('Something went wrong. Accounting Group can not be edit.'));

            return redirect('accounting-groups');
        }

        return view('accounting::accounting_groups.edit', [
            'accountingGroup' => $accountingGroup,
        ]);
    }

    public function update(AccountingGroupUpdateRequest $request, int $id): Redirector|RedirectResponse
    {
        $accountingGroup = $this->accountingGroupService->findAccountingGroupById($id);

        if (! $accountingGroup) {
            Alert::error('Error', _lang('Something went wrong. Accounting Group can not be updated.'));

            return redirect('accounting-groups');
        }

        $this->accountingGroupService->updateAccountingGroup($request->validated(), $id);

        Alert::toast('Accounting Group has been updated.', 'success');

        return redirect('accounting-groups');
    }

    public function destroy(int $id): Redirector|RedirectResponse
    {
        $accountingGroup = $this->accountingGroupService->deleteAccountingGroupById($id);

        if (! $accountingGroup) {
            Alert::error('Error', _lang('Something went wrong. Accounting Group can not be delete.'));

            return redirect('accounting-groups');
        }

        Alert::toast('Accounting Group has been delete successfully.', 'success');

        return redirect('accounting-groups');
    }

    public function getAccountGroups($id): JsonResponse
    {
        // Fetch account groups based on the selected category
        $accountGroups = AccountingGroup::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->where('accounting_category_id', $id)
            ->orderBy('name', 'asc')
            ->pluck('name', 'id')
            ->toArray();

        // Return the account groups as JSON
        return response()->json($accountGroups);
    }
}
