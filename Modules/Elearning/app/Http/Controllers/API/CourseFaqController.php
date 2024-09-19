<?php

namespace Modules\Elearning\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\RequestSanitizerTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Modules\Elearning\Http\Requests\CourseFaq\CourseFaqStoreRequest;
use Modules\Elearning\Http\Requests\CourseFaq\CourseFaqUpdateRequest;
use Modules\Elearning\Repositories\CourseFaqRepository;

class CourseFaqController extends Controller
{
    use RequestSanitizerTrait;

    public function __construct(private CourseFaqRepository $courseFaqRepository) {}

    public function index(): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->courseFaqRepository->getAll(request()->all()),
                'Course Faq has been fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function store(CourseFaqStoreRequest $request): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->courseFaqRepository->create($request->all()),
                'Course Faq has been created successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->courseFaqRepository->getById($id),
                'Course Faq has been fetched successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function update(CourseFaqUpdateRequest $request, int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->courseFaqRepository->update($id, $this->getUpdateRequest($request)),
                'Course Faq has been updated successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->courseFaqRepository->delete($id),
                'Course Faq has been deleted successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }
}
