<?php

namespace Modules\Finance\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Finance\Http\Requests\FeeHeadCreateRequest;
use Modules\Finance\Http\Requests\FeeHeadUpdateRequest;
use Modules\Finance\Models\FeeHead;
use Modules\Finance\Services\FeeHeadService;
use Modules\Finance\Services\FeeSubHeadService;

class FeeHeadController extends Controller
{
    public function __construct(
        private readonly FeeHeadService $feeHeadService,
        private readonly FeeSubHeadService $feeSubHeadService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->per_page ?: 10;
        try {
            $feeHeads = FeeHead::where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->orderBy('id', 'DESC')->paginate($perPage);

            return $this->responseSuccess(
                $feeHeads,
                _lang('Fee Head has been fetched successfully.')
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function store(FeeHeadCreateRequest $request): JsonResponse
    {
        $fee_head = $this->feeHeadService->createFeeHead($request->validated());

        if (! $fee_head) {
            return $this->responseError([], _lang('Something went wrong. FeeHead can not be create.'));
        }

        return $this->responseSuccess(
            [],
            _lang('Fee Head has been fetched successfully.')
        );
    }

    public function show(int $id): JsonResponse
    {
        $feeHead = $this->feeHeadService->findFeeHeadById($id);
        if (! $feeHead) {
            return $this->responseError([], _lang('Something went wrong. FeeHead can not be create.'));
        }

        return $this->responseSuccess(
            $feeHead,
            _lang('Fee Head has been fetched successfully.')
        );
    }

    public function update(FeeHeadUpdateRequest $request, int $id): JsonResponse
    {
        $feeHead = $this->feeHeadService->findFeeHeadById($id);
        if (! $feeHead) {
            return $this->responseError([], _lang('Something went wrong. FeeHead can not be update.'));
        }

        $this->feeHeadService->updateFeeHead($request->validated(), $id);

        return $this->responseSuccess(
            [],
            _lang('Fee Head has been update successfully.')
        );
    }

    public function feeHeadDelete(int $id): JsonResponse
    {
        $fee_head = $this->feeHeadService->deleteFeeHeadById($id);
        if (! $fee_head) {
            return $this->responseError([], _lang('Something went wrong. FeeHead can not be delete.'));
        }

        return $this->responseSuccess(
            [],
            _lang('Fee Head has been delete successfully.')
        );
    }

    public function serialUpdate(Request $request): JsonResponse
    {
        $feeHeadIds = $request->fee_head_ids;
        $serials = $request->serials;

        foreach ($feeHeadIds as $index => $feeHeadId) {
            $serial = $serials[$index]; // Get the corresponding serial from the serials array
            $feeHead = $this->feeHeadService->findFeeHeadById($feeHeadId);
            $feeHead->update(['serial' => $serial]);
        }

        return $this->responseSuccess(
            [],
            _lang('Fee Head has been serial update successfully.')
        );
    }
}
