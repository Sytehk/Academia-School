<?php

namespace Modules\Academic\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Modules\Academic\Http\Requests\OptionalSubjectConfig\OptionalSubjectConfigStoreRequest;
use Modules\Academic\Http\Requests\OptionalSubjectConfig\OptionalSubjectConfigUpdateRequest;
use Modules\Academic\Repositories\OptionalSubjectConfigRepository;

class OptionalSubjectConfigController extends Controller
{
    public function __construct(private OptionalSubjectConfigRepository $repo) {}

    public function index(): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->getAll(request()->all()), 'OptionalSubjectConfig has been fetched successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function store(OptionalSubjectConfigStoreRequest $request): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->create($request->all()), 'OptionalSubjectConfig has been created successfully.');
        } catch (QueryException $exception) {
            return $this->responseError([], 'Database error: '.$exception->getMessage());
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function show($id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->getById($id), 'OptionalSubjectConfig has been fetched successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function update(OptionalSubjectConfigUpdateRequest $request, $id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->update($id, $request->all()), 'OptionalSubjectConfig has been updated successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->delete($id), 'OptionalSubjectConfig has been deleted successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }
}
