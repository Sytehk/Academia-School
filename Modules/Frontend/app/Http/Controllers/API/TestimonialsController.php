<?php

namespace Modules\Frontend\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\RequestSanitizerTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Modules\Frontend\Http\Requests\Testimonial\TestimonialStoreRequest;
use Modules\Frontend\Http\Requests\Testimonial\TestimonialUpdateRequest;
use Modules\Frontend\Repositories\TestimonialRepository;

class TestimonialsController extends Controller
{
    use RequestSanitizerTrait;

    public function __construct(private TestimonialRepository $testimonial) {}

    public function index(): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->testimonial->getAll(request()->all()),
                'Testimonial has been fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function store(TestimonialStoreRequest $request): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->testimonial->create($request->all()),
                'Testimonial has been created successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->testimonial->getById($id),
                'Testimonial has been fetched successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function update(TestimonialUpdateRequest $request, int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->testimonial->update($id, $this->getUpdateRequest($request)),
                'Testimonial has been updated successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->testimonial->delete($id),
                'Testimonial has been deleted successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }
}
