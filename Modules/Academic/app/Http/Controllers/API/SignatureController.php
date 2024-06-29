<?php

namespace Modules\Academic\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Academic\Http\Requests\SignatureCreateRequest;
use Modules\Academic\Services\SignatureService;

class SignatureController extends Controller
{
    public function __construct(private readonly SignatureService $signatureService) {}

    public function index(): JsonResponse
    {
        $signatures = $this->signatureService->getSignatures();

        return $this->responseSuccess($signatures, 'Signatures have been fetched successfully.');
    }

    public function store(SignatureCreateRequest $request): JsonResponse
    {
        $signature = $this->signatureService->createSignature($request->validated());

        if (! $signature) {
            return $this->responseError($signature, _lang('Something went wrong !'), 404);
        }

        return $this->responseSuccess([], 'Signature application has been create successfully.');
    }

    public function show(int $id): JsonResponse
    {
        $signature = $this->signatureService->findSignatureById($id);
        if (! $signature) {
            return $this->responseError([], _lang('Something went wrong !'), 404);
        }

        return $this->responseSuccess($signature, 'Signature application has been show.');
    }

    public function update(SignatureCreateRequest $request, int $id): JsonResponse
    {
        $signature = $this->signatureService->findSignatureById($id);
        if (! $signature) {
            return $this->responseError([], _lang('Something went wrong!'));
        }

        // Was $request->all() — let a client add hidden institute_id/branch_id/user_id
        // fields to the POST body and reassign the signature to another tenant/user.
        $this->signatureService->updateSignature($request->validated(), $id);

        return $this->responseSuccess([], 'Signature application has been update.');
    }

    public function destroy(int $id): JsonResponse
    {
        $signature = $this->signatureService->deleteSignatureById($id);

        if (! $signature) {
            return $this->responseError([], _lang('Something went wrong!'));
        }

        return $this->responseSuccess([], 'Signature application has been delete.');
    }
}
