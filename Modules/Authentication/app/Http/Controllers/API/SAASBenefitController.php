<?php

namespace Modules\Authentication\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Modules\Authentication\Http\Requests\SAASBenefit\SAASBenefitStoreRequest;
use Modules\Authentication\Http\Requests\SAASBenefit\SAASBenefitUpdateRequest;
use Modules\Authentication\Repositories\SAASBenefitRepository;

class SAASBenefitController extends Controller
{
    public function __construct(private SAASBenefitRepository $repo) {}

    public function index(): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->getAll(request()->all()), 'SAASBenefit has been fetched successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function store(SAASBenefitStoreRequest $request): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->create($request->all()), 'SAASBenefit has been created successfully.');
        } catch (QueryException $exception) {
            return $this->responseError([], 'Database error: '.$exception->getMessage());
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function show($id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->getById($id), 'SAASBenefit has been fetched successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function update(SAASBenefitUpdateRequest $request, int $id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->update($id, $request->all()), 'SAASBenefit has been updated successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->delete($id), 'SAASBenefit has been deleted successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }
}
