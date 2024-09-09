<?php

namespace Modules\Transport\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\RequestSanitizerTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Modules\Transport\Http\Requests\Bus\BusStoreRequest;
use Modules\Transport\Http\Requests\Bus\BusUpdateRequest;
use Modules\Transport\Repositories\BusRepository;

class BusController extends Controller
{
    use RequestSanitizerTrait;

    public function __construct(private BusRepository $bus) {}

    public function index(): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->bus->getAll(request()->all()),
                'Bus has been fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function store(BusStoreRequest $request): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->bus->create($request->all()),
                'Bus has been created successfully.'
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
                'Bus has been fetched successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function update(BusUpdateRequest $request, int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->bus->update($id, $this->getUpdateRequest($request)),
                'Bus has been updated successfully.'
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
                'Bus has been deleted successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }
}
