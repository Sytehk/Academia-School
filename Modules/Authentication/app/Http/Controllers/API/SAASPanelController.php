<?php

namespace Modules\Authentication\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Modules\Authentication\Http\Requests\SAASPanel\SAASPanelStoreRequest;
use Modules\Authentication\Http\Requests\SAASPanel\SAASPanelUpdateRequest;
use Modules\Authentication\Repositories\SAASPanelRepository;

class SAASPanelController extends Controller
{
    public function __construct(private SAASPanelRepository $repo) {}

    public function index(): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->getAll(request()->all()), 'SAASPanel has been fetched successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function store(SAASPanelStoreRequest $request): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->create($request->all()), 'SAASPanel has been created successfully.');
        } catch (QueryException $exception) {
            return $this->responseError([], 'Database error: '.$exception->getMessage());
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function show($id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->getById($id), 'SAASPanel has been fetched successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function update(SAASPanelUpdateRequest $request, int $id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->update($id, $request->all()), 'SAASPanel has been updated successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->delete($id), 'SAASPanel has been deleted successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }
}
