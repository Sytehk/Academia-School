<?php

namespace Modules\Academic\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Modules\Academic\Http\Requests\WhatsAppSetting\WhatsAppSettingStoreRequest;
use Modules\Academic\Http\Requests\WhatsAppSetting\WhatsAppSettingUpdateRequest;
use Modules\Academic\Repositories\WhatsAppSettingRepository;

class WhatsAppSettingController extends Controller
{
    public function __construct(private WhatsAppSettingRepository $repo) {}

    public function index(): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->getAll(request()->all()), 'WhatsAppSetting has been fetched successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function store(WhatsAppSettingStoreRequest $request): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->create($request->all()), 'WhatsAppSetting has been created successfully.');
        } catch (QueryException $exception) {
            return $this->responseError([], 'Database error: '.$exception->getMessage());
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function show($id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->getById($id), 'WhatsAppSetting has been fetched successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function update(WhatsAppSettingUpdateRequest $request, $id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->update($id, $request->all()), 'WhatsAppSetting has been updated successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->delete($id), 'WhatsAppSetting has been deleted successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }
}
