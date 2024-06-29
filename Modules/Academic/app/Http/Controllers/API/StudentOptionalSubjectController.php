<?php

namespace Modules\Academic\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Modules\Academic\Http\Requests\StudentOptionalSubject\StudentOptionalSubjectStoreRequest;
use Modules\Academic\Http\Requests\StudentOptionalSubject\StudentOptionalSubjectUpdateRequest;
use Modules\Academic\Repositories\StudentOptionalSubjectRepository;

class StudentOptionalSubjectController extends Controller
{
    public function __construct(private StudentOptionalSubjectRepository $repo) {}

    public function index(): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->getAll(request()->all()), 'StudentOptionalSubject has been fetched successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function store(StudentOptionalSubjectStoreRequest $request): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->create($request->all()), 'StudentOptionalSubject has been created successfully.');
        } catch (QueryException $exception) {
            return $this->responseError([], 'Database error: '.$exception->getMessage());
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function show($id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->getById($id), 'StudentOptionalSubject has been fetched successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function update(StudentOptionalSubjectUpdateRequest $request, $id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->update($id, $request->all()), 'StudentOptionalSubject has been updated successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->delete($id), 'StudentOptionalSubject has been deleted successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }
}
