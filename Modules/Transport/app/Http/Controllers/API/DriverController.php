<?php

namespace Modules\Transport\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\RequestSanitizerTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Modules\Transport\Http\Requests\Driver\DriverStoreRequest;
use Modules\Transport\Http\Requests\Driver\DriverUpdateRequest;
use Modules\Transport\Repositories\DriverRepository;

class DriverController extends Controller
{
    use RequestSanitizerTrait;

    public function __construct(private DriverRepository $bus) {}

    public function index(): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->bus->getAll(request()->all()),
                'Driver has been fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function store(DriverStoreRequest $request): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->bus->create($request->all()),
                'Driver has been created successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->bus->getById($id),
                'Driver has been fetched successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function update(DriverUpdateRequest $request, int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->bus->update($id, $this->getUpdateRequest($request)),
                'Driver has been updated successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $bus = $this->bus->getById($id);
            if (! $bus) {
                return $this->responseError([], 'Institute Not Found');
            }

            return $this->responseSuccess(
                $this->bus->delete($id),
                'Driver has been deleted successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }
}
