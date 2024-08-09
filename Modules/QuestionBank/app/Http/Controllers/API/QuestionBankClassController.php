<?php

namespace Modules\QuestionBank\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\RequestSanitizerTrait;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Modules\QuestionBank\Http\Requests\QuestionBankClass\QuestionBankClassStoreRequest;
use Modules\QuestionBank\Http\Requests\QuestionBankClass\QuestionBankClassUpdateRequest;
use Modules\QuestionBank\Models\QuestionBankClass;
use Modules\QuestionBank\Repositories\QuestionBankClassRepository;

class QuestionBankClassController extends Controller
{
    use RequestSanitizerTrait;

    public function __construct(private QuestionBankClassRepository $repo) {}

    public function index(): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->getAll(request()->all()), 'QuestionBankClass has been fetched successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function store(QuestionBankClassStoreRequest $request): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->create($request->all()), 'QuestionBankClass has been created successfully.');
        } catch (QueryException $exception) {
            return $this->responseError([], 'Database error: '.$exception->getMessage());
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->getById($id), 'QuestionBankClass has been fetched successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function update(QuestionBankClassUpdateRequest $request, int $id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->update($id, $request->all()), 'QuestionBankClass has been updated successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $questionBankClass = QuestionBankClass::where('id', $id)->first();
        if (! $questionBankClass) {
            return $this->responseError([], 'Question Bank Class not found', 404);
        }
        if ($questionBankClass->groups->isNotEmpty() || $questionBankClass->subjects->isNotEmpty() || $questionBankClass->questions->isNotEmpty()) {
            return $this->responseError([], 'Question Bank Class cannot be deleted as it has associated groups,subjects or questions.', 400);
        }

        try {
            return $this->responseSuccess($this->repo->delete($id), 'QuestionBankClass has been deleted successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }
}
