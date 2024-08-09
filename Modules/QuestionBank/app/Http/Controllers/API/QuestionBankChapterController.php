<?php

namespace Modules\QuestionBank\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\RequestSanitizerTrait;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Modules\QuestionBank\Http\Requests\QuestionBankChapter\QuestionBankChapterStoreRequest;
use Modules\QuestionBank\Http\Requests\QuestionBankChapter\QuestionBankChapterUpdateRequest;
use Modules\QuestionBank\Models\QuestionBankChapter;
use Modules\QuestionBank\Repositories\QuestionBankChapterRepository;

class QuestionBankChapterController extends Controller
{
    use RequestSanitizerTrait;

    public function __construct(private QuestionBankChapterRepository $repo) {}

    public function index(): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->getAll(request()->all()), 'QuestionBankChapter has been fetched successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function store(QuestionBankChapterStoreRequest $request): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->create($request->all()), 'QuestionBankChapter has been created successfully.');
        } catch (QueryException $exception) {
            return $this->responseError([], 'Database error: '.$exception->getMessage());
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->getById($id), 'QuestionBankChapter has been fetched successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function update(QuestionBankChapterUpdateRequest $request, int $id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->update($id, $request->all()), 'QuestionBankChapter has been updated successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $questionBankChapter = QuestionBankChapter::where('id', $id)->first();
        if (! $questionBankChapter) {
            return $this->responseError([], 'Question Bank Class not found', 404);
        }
        if ($questionBankChapter->questions->isNotEmpty()) {
            return $this->responseError([], 'Question Bank Class cannot be deleted as it has associated questions.', 400);
        }

        try {
            return $this->responseSuccess($this->repo->delete($id), 'QuestionBankChapter has been deleted successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }
}
