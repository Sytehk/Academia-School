<?php

namespace Modules\SMS\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\SMS\Http\Requests\SmsTemplateRequest;
use Modules\SMS\Services\SmsTemplateService;

class SmsTemplateController extends Controller
{
    public function __construct(private readonly SmsTemplateService $smsTemplateService) {}

    public function index(): JsonResponse
    {
        $smsTemplates = $this->smsTemplateService->getSmsTemplates();

        return $this->responseSuccess($smsTemplates, 'SMS Templates fetch successfully.');
    }

    public function store(SmsTemplateRequest $request): JsonResponse
    {
        $this->smsTemplateService->store($request->validated());

        return $this->responseSuccess([], 'SMS Template Created Successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $template = $this->smsTemplateService->findById((int) $id);
        if (! $template) {
            return $this->responseError([], _lang('Something went wrong. SMS Template Not Found.'));
        }

        return $this->responseSuccess($template, 'SMS Template Show Successfully.');
    }

    public function update(SmsTemplateRequest $request, int $id): JsonResponse
    {
        $template = $this->smsTemplateService->findById($id);
        if (! $template) {
            return $this->responseError([], _lang('Something went wrong. SMS Template Not Found.'));
        }

        $this->smsTemplateService->update($request->validated(), $id);

        return $this->responseSuccess([], 'SMS Template Updated Successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        $template = $this->smsTemplateService->findById($id);
        if (! $template) {
            return $this->responseError([], _lang('Something went wrong. SMS Template Not Found.'));
        }

        $this->smsTemplateService->delete($id);

        return $this->responseSuccess([], 'SMS Template Deleted Successfully.');
    }
}
