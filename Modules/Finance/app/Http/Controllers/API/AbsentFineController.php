<?php

namespace Modules\Finance\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Modules\Academic\Models\AbsentFine;
use Modules\Finance\Services\AbsentFineService;

class AbsentFineController extends Controller
{
    public function __construct(private readonly AbsentFineService $absentFineService) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->per_page ?: 10;
        try {
            $absentFines = AbsentFine::where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->with('class', 'period')->orderBy('id', 'DESC')->paginate($perPage);

            return $this->responseSuccess(
                $absentFines,
                _lang('Absent Fines has been fetched successfully.')
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function store(Request $request): JsonResponse
    {
        // Define validation rules
        $validator = Validator::make($request->all(), [
            'class_id' => ['required', 'integer', 'exists:classes,id'],
            'period_id' => ['required', 'integer', 'exists:periods,id'],
            'amount' => ['required', 'numeric', 'min:0'], // Ensure a valid amount
            Rule::unique('absent_fines')->where(function ($query) use ($request) {
                return $query->where('class_id', $request->class_id)
                    ->where('period_id', $request->period_id);
            }),
        ], [
            'class_id.required' => 'Class is required.',
            'class_id.exists' => 'Selected class does not exist.',
            'period_id.required' => 'Period is required.',
            'period_id.exists' => 'Selected period does not exist.',
            'amount.required' => 'Amount is required.',
            'amount.numeric' => 'Amount must be a valid number.',
            'amount.min' => 'Amount cannot be negative.',
            'unique' => 'The combination of Class and Period already exists. Consider updating the record instead.',
        ]);

        // If validation fails, return a response with error messages
        if ($validator->fails()) {
            return $this->responseError([], $validator->errors()->first());
        }

        // If validation passes, proceed to check if the combination already exists
        $classId = intval($request->class_id);
        $periodId = intval($request->period_id);

        // Check if the combination already exists
        $existingRecord = $this->absentFineService->findByClassAndPeriod($classId, $periodId);

        if ($existingRecord) {
            return $this->responseError(
                [],
                _lang('The combination of Class and Period already exists. Consider updating the record instead.'),
                422
            );
        }

        // Data to be created
        $data = [
            'class_id' => $classId,
            'period_id' => $periodId,
            'fee_amount' => $request->amount,
        ];

        $feeHead = $this->absentFineService->createAbsentFine($data);

        if (! $feeHead) {
            return $this->responseError([], _lang('Something went wrong. Absent Fine could not be created.'), 422);
        }

        return $this->responseSuccess(
            [],
            _lang('Absent Fine has been created successfully.')
        );
    }

    public function show(int $id): JsonResponse
    {
        $absentFine = $this->absentFineService->findAbsentFineById($id);
        if (! $absentFine) {
            return $this->responseError([], _lang('Something went wrong. Absent Fine could not be found.'), 404);
        }

        return $this->responseSuccess(
            $absentFine,
            _lang('Absent Fine has been show successfully.')
        );
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            // Validate the incoming data
            $validator = Validator::make($request->all(), [
                'class_id' => ['required', 'integer', 'exists:classes,id'],
                'period_id' => ['required', 'integer', 'exists:periods,id'],
                'amount' => ['required', 'numeric', 'min:0'],
                Rule::unique('absent_fines')->where(function ($query) use ($request, $id) {
                    return $query->where('class_id', $request->class_id)
                        ->where('period_id', $request->period_id)
                        ->where('id', '!=', $id); // Ignore current record
                }),
            ], [
                'class_id.required' => 'Class is required.',
                'class_id.exists' => 'The selected class does not exist.',
                'period_id.required' => 'Period is required.',
                'period_id.exists' => 'The selected period does not exist.',
                'amount.required' => 'Amount is required.',
                'amount.numeric' => 'Amount must be a valid number.',
                'amount.min' => 'Amount must be a positive number.',
                'unique' => 'The combination of Class and Period already exists. Consider updating instead.',
            ]);

            if ($validator->fails()) {
                return $this->responseError([], $validator->errors()->first());
            }

            // Check if the absent fine exists
            $absentFine = $this->absentFineService->findAbsentFineById($id);
            if (! $absentFine) {
                return $this->responseError([], _lang('Something went wrong. Absent Fine could not be found.'), 404);
            }

            // Prepare the data to update
            $data = [
                'class_id' => $request->class_id,
                'period_id' => $request->period_id,
                'fee_amount' => $request->amount,
            ];

            // Update the absent fine
            $updatedFeeHead = $this->absentFineService->updateAbsentFine($data, $id);

            if (! $updatedFeeHead) {
                return $this->responseError([], _lang('Something went wrong. Absent Fine could not be updated.'));
            }

            return $this->responseSuccess([], _lang('Absent Fine has been updated successfully.'));
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $absentFine = AbsentFine::where('id', $id)->first();

        if (! empty($absentFine)) {
            $absentFine->delete();

            return $this->responseSuccess(
                [],
                _lang('Absent Fines has been delete successfully.')
            );
        } else {
            return $this->responseError([], _lang('Something went wrong. Absent Fine can not be delete.'));
        }
    }
}
