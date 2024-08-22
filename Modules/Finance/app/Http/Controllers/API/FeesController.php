<?php

namespace Modules\Finance\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Academic\Models\AbsentFine;
use Modules\Academic\Models\Section;
use Modules\Finance\Http\Requests\FeesCreateRequest;
use Modules\Finance\Models\Fee;
use Modules\Finance\Services\FeeService;

class FeesController extends Controller
{
    public function __construct(
        private readonly FeeService $feeService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->per_page ?: 10;
        $type = $request->type;

        try {
            // Initialize an empty variable for the result
            $data = [];

            // Fetch data based on the `type` parameter
            if ($type === 'absent_fine_amount') {
                $data = AbsentFine::orderBy('id', 'asc')->where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->paginate($perPage);
            } else {
                $data = Fee::with(
                    'class',
                    'section',
                    'group',
                    'studentCategory',
                    'feeHead',
                )->orderBy('id', 'asc')->where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->paginate($perPage);
            }

            return $this->responseSuccess(
                $data,
                _lang('Data has been fetched successfully.')
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function store(FeesCreateRequest $request): JsonResponse
    {
        $sections = Section::where('class_id', $request->class_id)
            ->when($request->group_id, function ($q) use ($request) {
                $q->where('student_group_id', $request->group_id);
            })
            ->get();

        if ($sections->isEmpty()) {
            return $this->responseError([], _lang('No section found for this class.'));
        }

        $createdFees = [];

        foreach ($sections as $section) {

            $data = $request->validated();

            $data['session_id'] = get_option('academic_year');
            $data['institute_id'] = get_institute_id();
            $data['branch_id'] = get_branch_id();
            $data['section_id'] = $section->id;

            $fee = Fee::updateOrCreate(
                [
                    'institute_id' => $data['institute_id'],
                    'branch_id' => $data['branch_id'],
                    'session_id' => $data['session_id'],
                    'class_id' => $data['class_id'],
                    'section_id' => $data['section_id'],
                    'fee_head_id' => $data['fee_head_id'],
                ],
                [
                    'group_id' => $data['group_id'] ?? null,
                    'student_category_id' => $data['student_category_id'] ?? null,
                    'fee_amount' => $data['fee_amount'],
                    'fine_amount' => $data['fine_amount'] ?? 0,
                    'fund_id' => $data['fund_id'] ?? null,
                ]
            );

            $createdFees[] = $fee;
        }

        return $this->responseSuccess(
            $createdFees,
            _lang('Fee has been created or updated successfully for all sections.')
        );
    }

    public function show(int $id): JsonResponse
    {
        $fee = $this->feeService->findFeeById((int) $id);

        return $this->responseSuccess(
            $fee,
            _lang('Fee has been show successfully')
        );
    }

    public function update(FeesCreateRequest $request, int $id): JsonResponse
    {
        $fee_head = $this->feeService->updateFee($request->validated(), $id);

        if (! $fee_head) {
            return $this->responseError([], _lang('Something went wrong. Fee can not be edit.'));
        }

        return $this->responseSuccess(
            [],
            _lang('Fee has been update successfully')
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $fee = Fee::where('id', $id)->first();

        if (! empty($fee)) {
            $fee->delete();

            return $this->responseSuccess(
                [],
                _lang('Fee has been delete successfully')
            );
        } else {
            return $this->responseError([], _lang('Something went wrong. Fee can not be delete.'));
        }
    }
}
