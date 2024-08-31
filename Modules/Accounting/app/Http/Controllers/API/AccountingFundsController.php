<?php

namespace Modules\Accounting\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Accounting\Http\Requests\AccountingFundCreateRequest;
use Modules\Accounting\Http\Requests\AccountingFundUpdateRequest;
use Modules\Accounting\Models\AccountingFund;
use Modules\Accounting\Services\AccountingFundService;

class AccountingFundsController extends Controller
{
    public function __construct(private readonly AccountingFundService $accountingFundService) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->per_page ?: 10;
        try {
            $funds = AccountingFund::orderBy('id', 'DESC')->paginate($perPage);

            return $this->responseSuccess(
                $funds,
                _lang('Accounting Funds has been fetched successfully.')
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function store(AccountingFundCreateRequest $request): JsonResponse
    {
        $accountingFund = $this->accountingFundService->createAccountingFund($request->validated());

        if (! $accountingFund) {
            return $this->responseError([], _lang('Something went wrong. Accounting Fund can not be create.'));
        }

        return $this->responseSuccess([], _lang('Accounting Fund has been create.'));
    }

    public function show(int $id): JsonResponse
    {
        $accountingFund = $this->accountingFundService->findAccountingFundById($id);

        if (! $accountingFund) {
            return $this->responseError([], _lang('Something went wrong. Accounting Fund can not be show.'));
        }

        return $this->responseSuccess($accountingFund, _lang('Accounting Fund has been show.'));
    }

    public function update(AccountingFundUpdateRequest $request, int $id): JsonResponse
    {
        $accountingFund = $this->accountingFundService->findAccountingFundById($id);

        if (! $accountingFund) {
            return $this->responseError([], _lang('Something went wrong. Accounting Fund can not be update.'));
        }

        $this->accountingFundService->updateAccountingFund($request->validated(), $id);

        return $this->responseSuccess([], _lang('Accounting Fund has been update.'));
    }

    public function destroy(int $id): JsonResponse
    {
        $accountingFund = $this->accountingFundService->deleteAccountingFundById($id);
        if (! $accountingFund) {
            return $this->responseError([], _lang('Something went wrong. Accounting Fund can not be delete.'));
        }

        return $this->responseSuccess([], _lang('Accounting Fund has been delete.'));
    }
}
