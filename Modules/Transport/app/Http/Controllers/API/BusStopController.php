<?php

namespace Modules\Transport\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\RequestSanitizerTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Modules\Transport\Http\Requests\BusStop\BusStopStoreRequest;
use Modules\Transport\Http\Requests\BusStop\BusStopUpdateRequest;
use Modules\Transport\Repositories\BusStopRepository;

class BusStopController extends Controller
{
    use RequestSanitizerTrait;

    public function __construct(private BusStopRepository $bus) {}

    public function index(): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->bus->getAll(request()->all()),
                'BusStop has been fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function store(BusStopStoreRequest $request): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->bus->create($request->all()),
                'BusStop has been created successfully.'
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
                'BusStop has been fetched successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function update(BusStopUpdateRequest $request, int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->bus->update($id, $this->getUpdateRequest($request)),
                'BusStop has been updated successfully.'
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
                'BusStop has been deleted successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }
}
