<?php

namespace Modules\Frontend\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\RequestSanitizerTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Modules\Frontend\Http\Requests\MobileAppSection\MobileAppSectionStoreRequest;
use Modules\Frontend\Http\Requests\MobileAppSection\MobileAppSectionUpdateRequest;
use Modules\Frontend\Repositories\MobileAppSectionRepository;

class MobileAppSectionController extends Controller
{
    use RequestSanitizerTrait;

    public function __construct(private MobileAppSectionRepository $faq) {}

    public function index(): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->faq->getAll(request()->all()),
                'MobileAppSection has been fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function store(MobileAppSectionStoreRequest $request): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->faq->create($request->all()),
                'MobileAppSection has been created successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->faq->getById($id),
                'MobileAppSection has been fetched successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function update(MobileAppSectionUpdateRequest $request, int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->faq->update($id, $this->getUpdateRequest($request)),
                'MobileAppSection has been updated successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->faq->delete($id),
                'MobileAppSection has been deleted successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }
}
