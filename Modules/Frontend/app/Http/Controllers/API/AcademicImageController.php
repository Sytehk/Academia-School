<?php

namespace Modules\Frontend\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\RequestSanitizerTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Modules\Frontend\Http\Requests\AcademicImage\AcademicImageStoreRequest;
use Modules\Frontend\Http\Requests\AcademicImage\AcademicImageUpdateRequest;
use Modules\Frontend\Repositories\AcademicImageRepository;

class AcademicImageController extends Controller
{
    use RequestSanitizerTrait;

    public function __construct(private AcademicImageRepository $academicImage) {}

    public function index(): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->academicImage->getAll(request()->all()),
                'AcademicImages has been fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function store(AcademicImageStoreRequest $request): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->academicImage->create($request->all()),
                'AcademicImages has been created successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->academicImage->getById($id),
                'AcademicImages has been fetched successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function update(AcademicImageUpdateRequest $request, int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->academicImage->update($id, $this->getUpdateRequest($request)),
                'AcademicImages has been updated successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->academicImage->delete($id),
                'AcademicImages has been deleted successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }
}
