<?php

namespace Modules\QuestionBank\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\RequestSanitizerTrait;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Modules\QuestionBank\Http\Requests\QuestionBankType\QuestionBankTypeStoreRequest;
use Modules\QuestionBank\Http\Requests\QuestionBankType\QuestionBankTypeUpdateRequest;
use Modules\QuestionBank\Repositories\QuestionBankTypeRepository;

class QuestionBankTypeController extends Controller
{
    use RequestSanitizerTrait;

    public function __construct(private QuestionBankTypeRepository $repo) {}

    public function index(): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->getAll(request()->all()), 'QuestionBank Type has been fetched successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function store(QuestionBankTypeStoreRequest $request): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->create($request->all()), 'QuestionBank Type has been created successfully.');
        } catch (QueryException $exception) {
            return $this->responseError([], 'Database error: '.$exception->getMessage());
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->getById($id), 'QuestionBank Type has been fetched successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function update(QuestionBankTypeUpdateRequest $request, int $id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->update($id, $request->all()), 'QuestionBank Type has been updated successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->delete($id), 'QuestionBank Type has been deleted successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }
}
