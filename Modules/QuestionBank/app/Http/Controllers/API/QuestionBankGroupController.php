<?php

namespace Modules\QuestionBank\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\RequestSanitizerTrait;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Modules\QuestionBank\Http\Requests\QuestionBankGroup\QuestionBankGroupStoreRequest;
use Modules\QuestionBank\Http\Requests\QuestionBankGroup\QuestionBankGroupUpdateRequest;
use Modules\QuestionBank\Models\QuestionBankGroup;
use Modules\QuestionBank\Repositories\QuestionBankGroupRepository;

class QuestionBankGroupController extends Controller
{
    use RequestSanitizerTrait;

    public function __construct(private QuestionBankGroupRepository $repo) {}

    public function index(): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->getAll(request()->all()), 'QuestionBankGroup has been fetched successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function store(QuestionBankGroupStoreRequest $request): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->create($request->all()), 'QuestionBankGroup has been created successfully.');
        } catch (QueryException $exception) {
            return $this->responseError([], 'Database error: '.$exception->getMessage());
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->getById($id), 'QuestionBankGroup has been fetched successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function update(QuestionBankGroupUpdateRequest $request, int $id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->update($id, $request->all()), 'QuestionBankGroup has been updated successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $questionBankGroup = QuestionBankGroup::where('id', $id)->first();
        if (! $questionBankGroup) {
            return $this->responseError([], 'Question Bank Class not found', 404);
        }
        if ($questionBankGroup->subjects->isNotEmpty() || $questionBankGroup->questions->isNotEmpty()) {
            return $this->responseError([], 'Question Bank Class cannot be deleted as it has associated subjects or questions.', 400);
        }

        // subjects
        try {
            return $this->responseSuccess($this->repo->delete($id), 'QuestionBankGroup has been deleted successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }
}
