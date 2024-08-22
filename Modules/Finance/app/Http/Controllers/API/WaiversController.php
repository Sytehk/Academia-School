<?php

namespace Modules\Finance\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Finance\Http\Requests\WaiverCreateRequest;
use Modules\Finance\Http\Requests\WaiverUpdateRequest;
use Modules\Finance\Models\Waiver;
use Modules\Finance\Services\WaiverService;

class WaiversController extends Controller
{
    public function __construct(private readonly WaiverService $waiverService) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->per_page ?: 10;
        try {
            $waivers = Waiver::where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->orderBy('id', 'DESC')->paginate($perPage);

            return $this->responseSuccess(
                $waivers,
                _lang('Waivers has been fetched successfully.')
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function store(WaiverCreateRequest $request): JsonResponse
    {
        $waiver = $this->waiverService->createWaiver($request->validated());

        if (! $waiver) {
            return $this->responseError([], _lang('Something went wrong. Waiver can not be create.'));
        }

        return $this->responseSuccess(
            [],
            _lang('Waiver has been create successfully.')
        );
    }

    public function show(int $id): JsonResponse
    {
        $waiver = $this->waiverService->findWaiverById($id);

        if (! $waiver) {
            return $this->responseError([], _lang('Something went wrong. Waiver can not be show.'));
        }

        return $this->responseSuccess(
            $waiver,
            _lang('Waiver has been show successfully.')
        );
    }

    public function update(WaiverUpdateRequest $request, int $id): JsonResponse
    {
        $waiver = $this->waiverService->findWaiverById($id);

        if (! $waiver) {
            return $this->responseError([], _lang('Something went wrong. Waiver can not be update.'));
        }

        $this->waiverService->updateWaiver($request->validated(), $id);

        return $this->responseSuccess(
            [],
            _lang('Waiver has been update successfully.')
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $waiver = $this->waiverService->deleteWaiverById($id);

        if (! $waiver) {
            return $this->responseError([], _lang('Something went wrong. Waiver can not be destroy.'));
        }

        return $this->responseSuccess(
            [],
            _lang('Waiver has been destroy successfully.')
        );
    }
}
