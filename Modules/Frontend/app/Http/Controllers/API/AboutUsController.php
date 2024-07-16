<?php

namespace Modules\Frontend\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\RequestSanitizerTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Modules\Frontend\Http\Requests\AboutUs\AboutUsStoreRequest;
use Modules\Frontend\Http\Requests\AboutUs\AboutUsUpdateRequest;
use Modules\Frontend\Repositories\AboutUsRepository;

class AboutUsController extends Controller
{
    use RequestSanitizerTrait;

    public function __construct(private AboutUsRepository $faq) {}

    public function index(): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->faq->getAll(request()->all()),
                'AboutUs has been fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function store(AboutUsStoreRequest $request): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->faq->create($request->all()),
                'AboutUs has been created successfully.'
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
                'AboutUs has been fetched successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function update(AboutUsUpdateRequest $request, int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->faq->update($id, $this->getUpdateRequest($request)),
                'AboutUs has been updated successfully.'
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
                'AboutUs has been deleted successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }
}
