<?php

namespace Modules\Academic\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Academic\Models\Period;
use Modules\Academic\Services\PeriodService;

class APIPeriodController extends Controller
{
    public function __construct(
        private readonly PeriodService $periodService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->per_page ?: 10;
        $periods = Period::select('id', 'period', 'serial_no')->paginate($perPage);

        return $this->responseSuccess($periods, 'Periods has been fetched successfully.');
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'period' => [
                'required',
                'max:50',
            ],
            'serial_no' => 'nullable|numeric',
        ]);

        $period = new Period;
        $period->institute_id = get_institute_id();
        $period->branch_id = get_branch_id();
        $period->serial_no = (int) $request->serial_no;
        $period->period = $request->period;
        $period->save();

        return $this->responseSuccess([], 'Periods has been added.');
    }

    public function show(int $id): JsonResponse
    {
        $period = $this->periodService->findPeriodById($id);

        if (! $period) {
            return $this->responseError([], 'Something went wrong. Period can not be show.', 404);
        }

        return $this->responseSuccess($period, 'Periods has been fetch.');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'period' => 'required|max:50',
            'serial_no' => 'nullable|numeric',
        ]);

        $period = $this->periodService->findPeriodById($id);

        if (! $period) {
            return $this->responseError([], 'Something went wrong. Period can not be updated.', 404);
        }

        $this->periodService->updatePeriod($request->all(), $id);

        return $this->responseSuccess([], 'Periods has been updated.');
    }

    public function destroy(int $id): JsonResponse
    {
        $fee_head = $this->periodService->deletePeriodById($id);

        if (! $fee_head) {
            return $this->responseError([], 'Something went wrong. Period can not be deleted.', 404);
        }

        return $this->responseSuccess([], 'Periods has been deleted.');
    }
}
