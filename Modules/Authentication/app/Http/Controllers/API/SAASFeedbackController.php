<?php

namespace Modules\Authentication\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Modules\Authentication\Http\Requests\SAASFeedback\SAASFeedbackStoreRequest;
use Modules\Authentication\Http\Requests\SAASFeedback\SAASFeedbackUpdateRequest;
use Modules\Authentication\Repositories\SAASFeedbackRepository;

class SAASFeedbackController extends Controller
{
    public function __construct(private SAASFeedbackRepository $repo) {}

    public function index(): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->getAll(request()->all()), 'SAASFeedback has been fetched successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function store(SAASFeedbackStoreRequest $request): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->create($request->all()), 'SAASFeedback has been created successfully.');
        } catch (QueryException $exception) {
            return $this->responseError([], 'Database error: '.$exception->getMessage());
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function show($id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->getById($id), 'SAASFeedback has been fetched successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function update(SAASFeedbackUpdateRequest $request, int $id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->update($id, $request->all()), 'SAASFeedback has been updated successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->delete($id), 'SAASFeedback has been deleted successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }
}
