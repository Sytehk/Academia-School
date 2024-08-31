<?php

namespace Modules\Accounting\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Accounting\Http\Requests\AccountingGroupCreateRequest;
use Modules\Accounting\Http\Requests\AccountingGroupUpdateRequest;
use Modules\Accounting\Models\AccountingGroup;
use Modules\Accounting\Services\AccountingGroupService;

class AccountingGroupsController extends Controller
{
    public function __construct(private readonly AccountingGroupService $accountingGroupService) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->per_page ?: 10;
        try {
            $groups = AccountingGroup::with('accountingCategory')->orderBy('id', 'DESC')->paginate($perPage);

            return $this->responseSuccess(
                $groups,
                _lang('Accounting Group has been fetched successfully.')
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function store(AccountingGroupCreateRequest $request): JsonResponse
    {
        $accountingGroup = $this->accountingGroupService->createAccountingGroup($request->validated());

        if (! $accountingGroup) {
            return $this->responseError([], _lang('Something went wrong. Accounting Group can not be create.'));
        }

        return $this->responseSuccess([], _lang('Accounting Group has been create.'));
    }

    public function show(int $id): JsonResponse
    {
        $accountingGroup = $this->accountingGroupService->findAccountingGroupById($id);

        if (! $accountingGroup) {
            return $this->responseError([], _lang('Something went wrong. Accounting Group can not be show.'));
        }

        return $this->responseSuccess($accountingGroup, _lang('Accounting Group has been show.'));
    }

    public function update(AccountingGroupUpdateRequest $request, int $id): JsonResponse
    {
        $accountingGroup = $this->accountingGroupService->findAccountingGroupById($id);

        if (! $accountingGroup) {
            return $this->responseError([], _lang('Something went wrong. Accounting Group can not be update.'));
        }

        $this->accountingGroupService->updateAccountingGroup($request->validated(), $id);

        return $this->responseSuccess([], _lang('Accounting Group has been update.'));
    }

    public function destroy(int $id): JsonResponse
    {
        $accountingGroup = $this->accountingGroupService->deleteAccountingGroupById($id);

        if (! $accountingGroup) {
            return $this->responseError([], _lang('Something went wrong. Accounting Group can not be delete.'));
        }

        return $this->responseSuccess([], _lang('Accounting Group has been delete.'));
    }

    public function getAccountGroups(int $id): JsonResponse
    {
        // Fetch account groups based on the selected category
        $accountGroups = AccountingGroup::where('accounting_category_id', $id)->orderBy('name', 'asc')->pluck('name', 'id')->toArray();

        return $this->responseSuccess($accountGroups, _lang('Accounting Groups fetch.'));
    }
}
