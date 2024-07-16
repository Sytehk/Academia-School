<?php

namespace Modules\Frontend\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\RequestSanitizerTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Modules\Frontend\Http\Requests\Banner\BannerStoreRequest;
use Modules\Frontend\Http\Requests\Banner\BannerUpdateRequest;
use Modules\Frontend\Repositories\BannerRepository;

class BannerController extends Controller
{
    use RequestSanitizerTrait;

    public function __construct(private BannerRepository $banner) {}

    public function index(): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->banner->getAll(request()->all()),
                'Banner has been fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function store(BannerStoreRequest $request): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->banner->create($request->all()),
                'Banner has been created successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->banner->getById($id),
                'Banner has been fetched successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function update(BannerUpdateRequest $request, int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->banner->update($id, $this->getUpdateRequest($request)),
                'Banner has been updated successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $banner = $this->banner->getById($id);
            if (! $banner) {
                return $this->responseError([], 'Institute Not Found');
            }
            if ($banner->image) {
                fileRemover('banners/', $banner->image);
            }

            return $this->responseSuccess(
                $this->banner->delete($id),
                'Banner has been deleted successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }
}
