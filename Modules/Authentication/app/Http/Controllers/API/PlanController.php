<?php

namespace Modules\Authentication\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\RequestSanitizerTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Modules\Authentication\Http\Requests\Plan\PlanStoreRequest;
use Modules\Authentication\Http\Requests\Plan\PlanUpdateRequest;
use Modules\Authentication\Repositories\PlanRepository;

class PlanController extends Controller
{
    use RequestSanitizerTrait;

    public function __construct(private PlanRepository $plan) {}

    public function index(): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->plan->getAll(request()->all()),
                'Plan has been fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function store(PlanStoreRequest $request): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->plan->create($request->all()),
                'Plan has been created successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->plan->getById($id),
                'Plan has been fetched successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function update(PlanUpdateRequest $request, int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->plan->update($id, $this->getUpdateRequest($request)),
                'Plan has been updated successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $plan = $this->plan->getById($id);
            if (! $plan) {
                return $this->responseError([], 'Plan Not Found');
            }

            return $this->responseSuccess(
                $this->plan->delete($id),
                'Plan has been deleted successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }
}
