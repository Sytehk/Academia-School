<?php

namespace Modules\Elearning\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\RequestSanitizerTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Modules\Elearning\Http\Requests\CourseFeature\CourseFeatureStoreRequest;
use Modules\Elearning\Http\Requests\CourseFeature\CourseFeatureUpdateRequest;
use Modules\Elearning\Repositories\CourseFeatureRepository;

class CourseFeatureController extends Controller
{
    use RequestSanitizerTrait;

    public function __construct(private CourseFeatureRepository $courseFeatureRepository) {}

    public function index(): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->courseFeatureRepository->getAll(request()->all()),
                'Course Feature has been fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function store(CourseFeatureStoreRequest $request): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->courseFeatureRepository->create($request->all()),
                'Course Feature has been created successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->courseFeatureRepository->getById($id),
                'Course Feature has been fetched successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function update(CourseFeatureUpdateRequest $request, int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->courseFeatureRepository->update($id, $this->getUpdateRequest($request)),
                'Course Feature has been updated successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->courseFeatureRepository->delete($id),
                'Course Feature has been deleted successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }
}
