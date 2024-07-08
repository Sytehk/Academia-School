<?php

namespace Modules\Authentication\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Modules\Authentication\Http\Requests\SAASSolution\SAASSolutionStoreRequest;
use Modules\Authentication\Http\Requests\SAASSolution\SAASSolutionUpdateRequest;
use Modules\Authentication\Repositories\SAASSolutionRepository;

class SAASSolutionController extends Controller
{
    public function __construct(private SAASSolutionRepository $repo) {}

    public function index(): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->getAll(request()->all()), 'SAASSolution has been fetched successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function store(SAASSolutionStoreRequest $request): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->create($request->all()), 'SAASSolution has been created successfully.');
        } catch (QueryException $exception) {
            return $this->responseError([], 'Database error: '.$exception->getMessage());
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function show($id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->getById($id), 'SAASSolution has been fetched successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function update(SAASSolutionUpdateRequest $request, int $id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->update($id, $request->all()), 'SAASSolution has been updated successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->delete($id), 'SAASSolution has been deleted successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }
}
