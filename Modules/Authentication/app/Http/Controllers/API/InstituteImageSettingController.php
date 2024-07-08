<?php

namespace Modules\Authentication\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Modules\Authentication\Http\Requests\InstituteImageSetting\InstituteImageSettingStoreRequest;
use Modules\Authentication\Http\Requests\InstituteImageSetting\InstituteImageSettingUpdateRequest;
use Modules\Authentication\Models\InstituteImageSetting;
use Modules\Authentication\Repositories\InstituteImageSettingRepository;

class InstituteImageSettingController extends Controller
{
    public function __construct(private InstituteImageSettingRepository $repo) {}

    public function index(): JsonResponse
    {
        try {
            $instituteImageSetting = InstituteImageSetting::where('institute_id', get_institute_id())->first();

            return $this->responseSuccess($instituteImageSetting, 'InstituteImageSetting has been fetched successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function store(InstituteImageSettingStoreRequest $request): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->create($request->all()), 'InstituteImageSetting has been created successfully.');
        } catch (QueryException $exception) {
            return $this->responseError([], 'Database error: '.$exception->getMessage());
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->getById($id), 'InstituteImageSetting has been fetched successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function update(InstituteImageSettingUpdateRequest $request, int $id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->update($id, $request->all()), 'InstituteImageSetting has been updated successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->delete($id), 'InstituteImageSetting has been deleted successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }
}
