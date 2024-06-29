<?php

namespace Modules\Academic\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Modules\Academic\Http\Requests\WhatsAppTemplate\WhatsAppTemplateStoreRequest;
use Modules\Academic\Http\Requests\WhatsAppTemplate\WhatsAppTemplateUpdateRequest;
use Modules\Academic\Repositories\WhatsAppTemplateRepository;

class WhatsAppTemplateController extends Controller
{
    public function __construct(private WhatsAppTemplateRepository $repo) {}

    public function index(): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->getAll(request()->all()), 'WhatsAppTemplate has been fetched successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function store(WhatsAppTemplateStoreRequest $request): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->create($request->all()), 'WhatsAppTemplate has been created successfully.');
        } catch (QueryException $exception) {
            return $this->responseError([], 'Database error: '.$exception->getMessage());
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function show($id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->getById($id), 'WhatsAppTemplate has been fetched successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function update(WhatsAppTemplateUpdateRequest $request, $id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->update($id, $request->all()), 'WhatsAppTemplate has been updated successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            return $this->responseSuccess($this->repo->delete($id), 'WhatsAppTemplate has been deleted successfully.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }
}
