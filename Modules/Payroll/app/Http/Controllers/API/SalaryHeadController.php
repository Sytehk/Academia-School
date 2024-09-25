<?php

namespace Modules\Payroll\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Payroll\Http\Requests\SalaryHeadCreateRequest;
use Modules\Payroll\Services\SalaryHeadService;

class SalaryHeadController extends Controller
{
    public function __construct(
        private readonly SalaryHeadService $salaryHead
    ) {}

    public function index(): JsonResponse
    {
        $salaryHeads = $this->salaryHead->getSalaryHeads();

        return $this->responseSuccess(
            $salaryHeads,
            _lang('Salary Head fetch successfully.')
        );
    }

    public function store(SalaryHeadCreateRequest $request): JsonResponse
    {
        $salaryHead = $this->salaryHead->createSalaryHead($request->validated());
        if (! $salaryHead) {
            return $this->responseError([], _lang('Something went wrong. Salary head can not be submitted.'));
        }

        return $this->responseSuccess(
            [],
            _lang('Salary head application has been submitted.')
        );
    }

    public function show(int $id): JsonResponse
    {
        $salaryHead = $this->salaryHead->findSalaryHeadById($id);
        if (! $salaryHead) {
            return $this->responseError([], _lang('Something went wrong. Salary head can not be found.'), 404);
        }

        return $this->responseSuccess(
            $salaryHead,
            _lang('Salary head application has been fetch successfully.')
        );
    }

    public function update(SalaryHeadCreateRequest $request, int $id): JsonResponse
    {
        $salaryHead = $this->salaryHead->findSalaryHeadById($id);

        if (! $salaryHead) {
            return $this->responseError([], _lang('Something went wrong. Salary head can not be found.'), 404);
        }

        $this->salaryHead->updateSalaryHead($request->validated(), $id);

        return $this->responseSuccess(
            [],
            _lang('Salary head application has been update.')
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $salaryHead = $this->salaryHead->findSalaryHeadById($id);

        if (! $salaryHead) {
            return $this->responseError([], _lang('Something went wrong. Salary head can not be found.'), 404);
        }

        $this->salaryHead->deleteSalaryHeadById($id);

        return $this->responseSuccess(
            [],
            _lang('Salary head application has been deleted.')
        );
    }
}
