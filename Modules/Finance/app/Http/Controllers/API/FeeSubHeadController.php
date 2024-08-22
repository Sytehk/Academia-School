<?php

namespace Modules\Finance\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Finance\Http\Requests\FeeSubHeadCreateRequest;
use Modules\Finance\Http\Requests\FeeSubHeadUpdateRequest;
use Modules\Finance\Models\FeeSubHead;
use Modules\Finance\Services\FeeSubHeadService;

class FeeSubHeadController extends Controller
{
    public function __construct(private readonly FeeSubHeadService $feeSubHeadService) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->per_page ?: 10;
        try {
            $feeSubHead = FeeSubHead::where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->orderBy('id', 'DESC')->paginate($perPage);

            return $this->responseSuccess(
                $feeSubHead,
                _lang('Fee Sub-Head has been fetched successfully.')
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function store(FeeSubHeadCreateRequest $request): JsonResponse
    {
        $fee_sub_head = $this->feeSubHeadService->createFeeSubHead($request->validated());

        if (! $fee_sub_head) {
            return $this->responseError([], _lang('Something went wrong. Fee-Sub-Head can not be create.'));
        }

        return $this->responseSuccess(
            [],
            _lang('Fee Sub-Head has been create successfully.')
        );
    }

    public function show(int $id): JsonResponse
    {
        $fee_sub_head = $this->feeSubHeadService->findFeeSubHeadById($id);

        if (! $fee_sub_head) {
            return $this->responseError([], _lang('Something went wrong. Fee-Sub-Head can not be show.'));
        }

        return $this->responseSuccess(
            $fee_sub_head,
            _lang('Fee Sub-Head has been show successfully.')
        );
    }

    public function update(FeeSubHeadUpdateRequest $request, int $id): JsonResponse
    {
        $fee_sub_head = $this->feeSubHeadService->findFeeSubHeadById($id);

        if (! $fee_sub_head) {
            return $this->responseError([], _lang('Something went wrong. Fee-Sub-Head can not be update.'));
        }

        $this->feeSubHeadService->updateFeeSubHead($request->validated(), $id);

        return $this->responseSuccess(
            [],
            _lang('Fee Sub-Head has been update successfully.')
        );
    }

    public function feeSubHeadDelete($id): JsonResponse
    {
        $fee_sub_head = $this->feeSubHeadService->deleteFeeSubHeadById($id);

        if (! $fee_sub_head) {
            return $this->responseError([], _lang('Something went wrong. Fee-Sub-Head can not be delete.'));
        }

        return $this->responseSuccess(
            [],
            _lang('Fee Sub-Head has been deleted successfully.')
        );
    }

    public function serialFeeSubHeadUpdate(Request $request): JsonResponse
    {
        $feeHeadIds = $request->fee_sub_head_ids;
        $serials = $request->serials;

        foreach ($feeHeadIds as $index => $feeHeadId) {
            $serial = $serials[$index];
            $department = $this->feeSubHeadService->findFeeSubHeadById($feeHeadId);
            $department->update(['serial' => $serial]);
        }

        return $this->responseSuccess(
            [],
            _lang('Fee Sub-Head has been fetched successfully.')
        );
    }
}
