<?php

namespace Modules\Accounting\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Accounting\Http\Requests\AccountingLedgerCreateRequest;
use Modules\Accounting\Http\Requests\AccountingLedgerUpdateRequest;
use Modules\Accounting\Models\AccountingLedger;
use Modules\Accounting\Services\AccountingCategoryService;
use Modules\Accounting\Services\AccountingLedgerService;

class AccountingLedgersController extends Controller
{
    public function __construct(
        private readonly AccountingLedgerService $accountingLedgerService,
        private readonly AccountingCategoryService $accountingCategoryService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->per_page ?: 10;
        $type = $request->type;

        // Initialize excluded category IDs
        $excluded_category_ids = [];

        // Determine excluded category IDs based on the type
        if ($type === 'payment') {
            $excluded_category_ids = [1, 2, 3, 4, 5, 6];
        } elseif ($type === 'receipt') {
            $excluded_category_ids = [1, 7];
        } elseif ($type === 'payment_by') {
            $excluded_category_ids = [2, 3, 4, 5, 6, 7];
        }

        try {
            // Build the query
            $query = AccountingLedger::query();

            // If there are excluded category IDs, apply the filter
            if (! empty($excluded_category_ids)) {
                $query->whereNotIn('accounting_category_id', $excluded_category_ids);
            }

            // Get the paginated results
            $ledgers = $query->orderBy('id', 'ASC')->with('accountingCategory', 'accountingGroup')->paginate($perPage);

            return $this->responseSuccess(
                $ledgers,
                _lang('Accounting Ledgers have been fetched successfully.')
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function store(AccountingLedgerCreateRequest $request): JsonResponse
    {
        $accountingLedger = $this->accountingLedgerService->createAccountingLedger($request->validated());

        if (! $accountingLedger) {
            return $this->responseError([], _lang('Something went wrong. Accounting Ledger can not be create.'));
        }

        return $this->responseSuccess([], _lang('Accounting Ledger has been create.'));
    }

    public function show(int $id): JsonResponse
    {
        $accountingLedger = $this->accountingLedgerService->findAccountingLedgerById($id);

        if (! $accountingLedger) {
            return $this->responseError([], _lang('Something went wrong. Accounting Ledger can not be show.'));
        }

        return $this->responseSuccess($accountingLedger, _lang('Accounting Ledger has been show.'));
    }

    public function update(AccountingLedgerUpdateRequest $request, int $id): JsonResponse
    {
        $accountingLedger = $this->accountingLedgerService->findAccountingLedgerById($id);

        if (! $accountingLedger) {
            return $this->responseError([], _lang('Something went wrong. Accounting Ledger can not be update.'));
        }

        $this->accountingLedgerService->updateAccountingLedger($request->validated(), $id);

        return $this->responseSuccess([], _lang('Accounting Ledger has been update.'));
    }

    public function destroy(int $id): JsonResponse
    {
        $accountingLedger = $this->accountingLedgerService->deleteAccountingLedgerById($id);

        if (! $accountingLedger) {
            return $this->responseError([], _lang('Something went wrong. Accounting Ledger can not be delete.'));
        }

        return $this->responseSuccess([], _lang('Accounting Ledger has been delete.'));
    }
}
