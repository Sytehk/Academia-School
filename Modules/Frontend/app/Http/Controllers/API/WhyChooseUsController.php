<?php

namespace Modules\Frontend\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\RequestSanitizerTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Modules\Frontend\Http\Requests\WhyChooseUs\WhyChooseUsStoreRequest;
use Modules\Frontend\Http\Requests\WhyChooseUs\WhyChooseUsUpdateRequest;
use Modules\Frontend\Repositories\WhyChooseUsRepository;

class WhyChooseUsController extends Controller
{
    use RequestSanitizerTrait;

    public function __construct(private WhyChooseUsRepository $faq) {}

    public function index(): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->faq->getAll(request()->all()),
                'WhyChooseUs has been fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function store(WhyChooseUsStoreRequest $request): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->faq->create($request->all()),
                'WhyChooseUs has been created successfully.'
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
                'WhyChooseUs has been fetched successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function update(WhyChooseUsUpdateRequest $request, int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->faq->update($id, $this->getUpdateRequest($request)),
                'WhyChooseUs has been updated successfully.'
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
                'WhyChooseUs has been deleted successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }
}
