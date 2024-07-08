<?php

namespace Modules\Authentication\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Modules\Authentication\Http\Requests\SAASFaq\SAASFaqStoreRequest;
use Modules\Authentication\Http\Requests\SAASFaq\SAASFaqUpdateRequest;
use Modules\Authentication\Repositories\SAASFaqRepository;

class SAASFaqController extends Controller
{
    public function __construct(private SAASFaqRepository $repo) {}

    public function index(): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->getAll(request()->all()), 'SAASFaq has been fetched successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function store(SAASFaqStoreRequest $request): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->create($request->all()), 'SAASFaq has been created successfully.');
        } catch (QueryException $exception) {
            return $this->responseError([], 'Database error: '.$exception->getMessage());
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function show($id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->getById($id), 'SAASFaq has been fetched successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function update(SAASFaqUpdateRequest $request, int $id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->update($id, $request->all()), 'SAASFaq has been updated successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->delete($id), 'SAASFaq has been deleted successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }
}
