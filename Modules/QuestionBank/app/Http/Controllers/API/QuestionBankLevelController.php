<?php

namespace Modules\QuestionBank\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\RequestSanitizerTrait;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Modules\QuestionBank\Http\Requests\QuestionBankLevel\QuestionBankLevelStoreRequest;
use Modules\QuestionBank\Http\Requests\QuestionBankLevel\QuestionBankLevelUpdateRequest;
use Modules\QuestionBank\Repositories\QuestionBankLevelRepository;

class QuestionBankLevelController extends Controller
{
    use RequestSanitizerTrait;

    public function __construct(private QuestionBankLevelRepository $repo) {}

    public function index(): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->getAll(request()->all()), 'QuestionBankLevel has been fetched successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function store(QuestionBankLevelStoreRequest $request): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->create($request->validated()), 'QuestionBankLevel has been created successfully.');
        } catch (QueryException $exception) {
            return $this->responseError([], 'Database error: '.$exception->getMessage());
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->getById($id), 'QuestionBankLevel has been fetched successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function update(QuestionBankLevelUpdateRequest $request, int $id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->update($id, $request->all()), 'QuestionBankLevel has been updated successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->delete($id), 'QuestionBankLevel has been deleted successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }
}
