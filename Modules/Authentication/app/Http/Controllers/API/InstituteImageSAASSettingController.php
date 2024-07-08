<?php

namespace Modules\Authentication\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Modules\Authentication\Http\Requests\InstituteImageSAASSetting\InstituteImageSAASSettingStoreRequest;
use Modules\Authentication\Http\Requests\InstituteImageSAASSetting\InstituteImageSAASSettingUpdateRequest;
use Modules\Authentication\Models\InstituteImageSAASSetting;
use Modules\Authentication\Repositories\InstituteImageSAASSettingRepository;

class InstituteImageSAASSettingController extends Controller
{
    public function __construct(private InstituteImageSAASSettingRepository $repo) {}

    public function index(): JsonResponse
    {
        try {
            $instituteImageSAASSetting = InstituteImageSAASSetting::first();

            return $this->responseSuccess($instituteImageSAASSetting, 'InstituteImageSAASSetting has been fetched successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function store(InstituteImageSAASSettingStoreRequest $request): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->create($request->all()), 'InstituteImageSAASSetting has been created successfully.');
        } catch (QueryException $exception) {
            return $this->responseError([], 'Database error: '.$exception->getMessage());
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->getById($id), 'InstituteImageSAASSetting has been fetched successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function update(InstituteImageSAASSettingUpdateRequest $request, int $id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->update($id, $request->all()), 'InstituteImageSAASSetting has been updated successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->delete($id), 'InstituteImageSAASSetting has been deleted successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }
}
