<?php

namespace Modules\Frontend\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\RequestSanitizerTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Modules\Frontend\Http\Requests\ReadyToJoinUs\ReadyToJoinUsStoreRequest;
use Modules\Frontend\Http\Requests\ReadyToJoinUs\ReadyToJoinUsUpdateRequest;
use Modules\Frontend\Repositories\ReadyToJoinUsRepository;

class ReadyToJoinUsController extends Controller
{
    use RequestSanitizerTrait;

    public function __construct(private ReadyToJoinUsRepository $readyToJoinUsRepository) {}

    public function index(): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->readyToJoinUsRepository->getAll(request()->all()),
                'ReadyToJoinUs has been fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function store(ReadyToJoinUsStoreRequest $request): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->readyToJoinUsRepository->create($request->all()),
                'ReadyToJoinUs has been created successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->readyToJoinUsRepository->getById($id),
                'ReadyToJoinUs has been fetched successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function update(ReadyToJoinUsUpdateRequest $request, int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->readyToJoinUsRepository->update($id, $this->getUpdateRequest($request)),
                'ReadyToJoinUs has been updated successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->readyToJoinUsRepository->delete($id),
                'ReadyToJoinUs has been deleted successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }
}
