<?php

namespace Modules\Transport\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\RequestSanitizerTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Modules\Transport\Http\Requests\TransportMember\TransportMemberStoreRequest;
use Modules\Transport\Http\Requests\TransportMember\TransportMemberUpdateRequest;
use Modules\Transport\Repositories\TransportMemberRepository;

class TransportMemberController extends Controller
{
    use RequestSanitizerTrait;

    public function __construct(private TransportMemberRepository $bus) {}

    public function index(): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->bus->getAll(request()->all()),
                'TransportMember has been fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function store(TransportMemberStoreRequest $request): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->bus->create($request->all()),
                'TransportMember has been created successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->bus->getById($id),
                'TransportMember has been fetched successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage());
        }
    }

    public function update(TransportMemberUpdateRequest $request, int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->bus->update($id, $this->getUpdateRequest($request)),
                'TransportMember has been updated successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $bus = $this->bus->getById($id);
            if (! $bus) {
                return $this->responseError([], 'Institute Not Found');
            }

            return $this->responseSuccess(
                $this->bus->delete($id),
                'TransportMember has been deleted successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }
}
