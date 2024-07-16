<?php

namespace Modules\Frontend\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\RequestSanitizerTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Modules\Frontend\Http\Requests\Faq\FaqStoreRequest;
use Modules\Frontend\Http\Requests\Faq\FaqUpdateRequest;
use Modules\Frontend\Repositories\FaqQuestionRepository;

class FaqQuestionController extends Controller
{
    use RequestSanitizerTrait;

    public function __construct(private FaqQuestionRepository $faqQuestion) {}

    public function index(): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->faqQuestion->getAll(request()->all()),
                'Faq has been fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function store(FaqStoreRequest $request): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->faqQuestion->create($request->all()),
                'Faq has been created successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->faqQuestion->getById($id),
                'Faq has been fetched successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function update(FaqUpdateRequest $request, int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->faqQuestion->update($id, $this->getUpdateRequest($request)),
                'Faq has been updated successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->faqQuestion->delete($id),
                'Faq has been deleted successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }
}
