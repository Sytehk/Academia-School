<?php

namespace Modules\Authentication\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\RequestSanitizerTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Modules\Authentication\Http\Requests\Institute\InstituteStoreRequest;
use Modules\Authentication\Http\Requests\Institute\InstituteUpdateRequest;
use Modules\Authentication\Models\Institute;
use Modules\Authentication\Repositories\InstituteRepository;
use Modules\Authentication\Services\InstituteProvisioningService;
use Modules\Authentication\Services\InstitutePurgeService;
use Throwable;

class InstituteController extends Controller
{
    use RequestSanitizerTrait;

    public function __construct(
        private InstituteRepository $institute,
        private InstituteProvisioningService $provisioner,
        private InstitutePurgeService $purge
    ) {}

    public function index(): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->institute->getAll(request()->all()),
                'Institute has been fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function store(InstituteStoreRequest $request): JsonResponse
    {
        try {
            $result = $this->provisioner->provisionFromAdmin(
                $request->all(),
                $request->user()?->id
            );

            return $this->responseSuccess(
                $result['institute']->load(['branches', 'owner']),
                'Institute has been created successfully.'
            );
        } catch (Throwable $exception) {
            $code = (int) $exception->getCode();
            $status = ($code >= 400 && $code < 600) ? $code : 500;

            return $this->responseError([], $exception->getMessage(), $status);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->institute->getById($id),
                'Institute has been fetched successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function update(InstituteUpdateRequest $request, int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->institute->update($id, $this->getUpdateRequest($request)),
                'Institute has been updated successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * Permanently remove an institute and every row attached to it (branches,
     * users, students, subscriptions, domain requests, per-institute roles,
     * uploaded files, all module data). Irreversible — this frees the unique
     * email/domain so the same school can be registered again.
     */
    public function destroy(int $id): JsonResponse
    {
        $institute = Institute::withTrashed()->find($id);

        if (! $institute) {
            return $this->responseError([], 'Institute Not Found', 404);
        }

        try {
            $this->purge->purge($id);

            return $this->responseSuccess(
                ['id' => $id],
                'Institute has been deleted successfully.'
            );
        } catch (Throwable $exception) {
            return $this->responseError([], $exception->getMessage(), 409);
        }
    }

    public function statusUpdate(int $instituteId): JsonResponse
    {
        try {
            $institute = Institute::find($instituteId);

            if ($institute) {
                // Null-safe: a missing status is treated as 0 (inactive), so the
                // first toggle activates it rather than relying on `null == 1`.
                $institute->status = (int) $institute->getRawOriginal('status') === 1 ? 0 : 1;
                $institute->save();

                return $this->responseSuccess(
                    ['status' => $institute->status],
                    'Institute status updated successfully.'
                );
            }

            return $this->responseError([], 'Institute not found.', 404);
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }
}
