<?php

namespace Modules\QuestionBank\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\RequestSanitizerTrait;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Modules\QuestionBank\Http\Requests\QuestionBankSubject\QuestionBankSubjectStoreRequest;
use Modules\QuestionBank\Http\Requests\QuestionBankSubject\QuestionBankSubjectUpdateRequest;
use Modules\QuestionBank\Models\QuestionBankSubject;
use Modules\QuestionBank\Repositories\QuestionBankSubjectRepository;

class QuestionBankSubjectController extends Controller
{
    use RequestSanitizerTrait;

    public function __construct(private QuestionBankSubjectRepository $repo) {}

    public function index(): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->getAll(request()->all()), 'QuestionBankSubject has been fetched successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function store(QuestionBankSubjectStoreRequest $request): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->create($request->all()), 'QuestionBankSubject has been created successfully.');
        } catch (QueryException $exception) {
            return $this->responseError([], 'Database error: '.$exception->getMessage());
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->getById($id), 'QuestionBankSubject has been fetched successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function update(QuestionBankSubjectUpdateRequest $request, int $id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->update($id, $request->all()), 'QuestionBankSubject has been updated successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $questionBankSubjects = QuestionBankSubject::where('id', $id)->first();
        if (! $questionBankSubjects) {
            return $this->responseError([], 'Question Bank Class not found', 404);
        }

        if ($questionBankSubjects->chapters->isNotEmpty()) {
            return $this->responseError([], 'Question Bank Class cannot be deleted as it has associated chapters or questions.', 400);
        }

        try {
            return $this->responseSuccess($this->repo->delete($id), 'QuestionBankSubject has been deleted successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }
}
