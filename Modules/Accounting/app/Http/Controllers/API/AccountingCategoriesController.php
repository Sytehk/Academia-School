<?php

namespace Modules\Accounting\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Accounting\Http\Requests\AccountingCategoryCreateRequest;
use Modules\Accounting\Http\Requests\AccountingCategoryUpdateRequest;
use Modules\Accounting\Models\AccountingCategory;
use Modules\Accounting\Services\AccountingCategoryService;

class AccountingCategoriesController extends Controller
{
    public function __construct(private readonly AccountingCategoryService $accountingCategoryService) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->per_page ?: 10;
        try {
            $categories = AccountingCategory::orderBy('id', 'DESC')->paginate($perPage);

            return $this->responseSuccess(
                $categories,
                _lang('Accounting Categories has been fetched successfully.')
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function store(AccountingCategoryCreateRequest $request): JsonResponse
    {
        $accountingCategory = $this->accountingCategoryService->createAccountingCategory($request->validated());

        if (! $accountingCategory) {
            return $this->responseError([], _lang('Something went wrong. Accounting Category can not be create.'));
        }

        return $this->responseSuccess([], _lang('Accounting Category has been create.'));
    }

    public function show(int $id): JsonResponse
    {
        $accountingCategory = $this->accountingCategoryService->findAccountingCategoryById($id);

        if (! $accountingCategory) {
            return $this->responseError([], _lang('Something went wrong. Accounting Category can not be show.'));
        }

        return $this->responseSuccess($accountingCategory, _lang('Accounting Category has been show.'));
    }

    public function update(AccountingCategoryUpdateRequest $request, int $id): JsonResponse
    {
        $accountingCategory = $this->accountingCategoryService->findAccountingCategoryById($id);

        if (! $accountingCategory) {
            return $this->responseError([], _lang('Something went wrong. Accounting Category can not be update.'));
        }

        $this->accountingCategoryService->updateAccountingCategory($request->validated(), $id);

        return $this->responseSuccess([], _lang('Accounting Category has been updated successfully.'));
    }

    public function destroy(int $id): JsonResponse
    {
        $accountingCategory = $this->accountingCategoryService->deleteAccountingCategoryById($id);

        if (! $accountingCategory) {
            return $this->responseError([], _lang('Something went wrong. Accounting Category can not be delete.'));
        }

        return $this->responseSuccess([], _lang('Accounting Category has been delete.'));
    }
}
