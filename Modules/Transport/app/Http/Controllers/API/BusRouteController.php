<?php

namespace Modules\Transport\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\RequestSanitizerTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Modules\Transport\Http\Requests\BusRoute\BusRouteStoreRequest;
use Modules\Transport\Http\Requests\BusRoute\BusRouteUpdateRequest;
use Modules\Transport\Repositories\BusRouteRepository;

class BusRouteController extends Controller
{
    use RequestSanitizerTrait;

    public function __construct(private BusRouteRepository $bus) {}

    public function index(): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->bus->getAll(request()->all()),
                'BusRoute has been fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function store(BusRouteStoreRequest $request): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->bus->create($request->all()),
                'BusRoute has been created successfully.'
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
                'BusRoute has been fetched successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function update(BusRouteUpdateRequest $request, int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->bus->update($id, $this->getUpdateRequest($request)),
                'BusRoute has been updated successfully.'
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
                'BusRoute has been deleted successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }
}
