<?php

namespace Modules\Frontend\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\RequestSanitizerTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Modules\Frontend\Http\Requests\Policy\PolicyStoreRequest;
use Modules\Frontend\Http\Requests\Policy\PolicyUpdateRequest;
use Modules\Frontend\Models\Policy;
use Modules\Frontend\Repositories\PolicyRepository;

class PolicyController extends Controller
{
    use RequestSanitizerTrait;

    public function __construct(private PolicyRepository $policy) {}

    public function index(): JsonResponse
    {
        try {
            $user = auth()->user();
            if ($user?->institute_id && $user?->branch_id) {
                Policy::ensureDefaultsFor(
                    (int) $user->institute_id,
                    (int) $user->branch_id
                );
            }

            return $this->responseSuccess(
                $this->policy->getAll(request()->all()),
                'Policy has been fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function store(PolicyStoreRequest $request): JsonResponse
    {
        return $this->responseError(
            [],
            'Default policy pages are assigned automatically and can only be edited.',
            403
        );
    }

    public function show(int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->policy->getById($id),
                'Policy has been fetched successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function update(PolicyUpdateRequest $request, int $id): JsonResponse
    {
        try {
            $payload = $this->getUpdateRequest($request);
            unset($payload['type']);

            return $this->responseSuccess(
                $this->policy->update($id, $payload),
                'Policy has been updated successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        return $this->responseError(
            [],
            'Default policy pages cannot be deleted.',
            403
        );
    }
}
