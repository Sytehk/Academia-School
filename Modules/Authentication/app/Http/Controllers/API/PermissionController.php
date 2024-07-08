<?php

namespace Modules\Authentication\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Authentication\Services\PermissionService;

class PermissionController extends Controller
{
    public function __construct(
        private PermissionService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = request('per_page') ?? 10;
        $permissions = $this->service->index($request, $perPage);

        if (! $permissions) {
            return $this->responseError([], 'No Data Found!', 404);
        }

        return $this->responseSuccess($permissions);
    }

    public function store(Request $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $this->service->create($request->validated());
            DB::commit();

            return $this->responseSuccess([]);
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->responseError([], 'Data Process Error!');
        }
    }
}
