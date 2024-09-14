<?php

namespace Modules\Teacher\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Teacher\Http\Requests\Behavior\BehaviorStoreRequest;
use Modules\Teacher\Http\Requests\Behavior\BehaviorUpdateRequest;
use Modules\Teacher\Services\Behavior\BehaviorService;

class BehaviorController extends Controller
{
    public function __construct(
        private BehaviorService $service
    ) {}

    public function all(Request $request): JsonResponse
    {
        $behaviors = $this->service->getAll($request);
        if (! $behaviors) {
            return $this->responseError([], 'No Data Found!');
        }

        return $this->responseSuccess($behaviors, 'Behavior has been fetch successfully.');
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = request('per_page') ?? 10;
        $behaviors = $this->service->index($request, $perPage);
        if (! $behaviors) {
            return $this->responseError([], 'No Data Found!');
        }

        return $this->responseSuccess($behaviors, 'Behavior has been fetch successfully.');
    }

    public function store(BehaviorStoreRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = $this->service->create($request->validated());
            DB::commit();

            return $this->responseSuccess($data, 'Behavior has been create successfully.');
        } catch (Exception $e) {
            DB::rollBack();

            return $this->responseError([], 'Data Process Error!');
        }
    }

    public function show(int $id): JsonResponse
    {
        $behavior = $this->service->getById($id);
        if (! $behavior) {
            return $this->responseError([], 'No Data Found!');
        }

        return $this->responseSuccess($behavior, 'Behavior has been show successfully.');
    }

    public function update(BehaviorUpdateRequest $request, int $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = $this->service->update($id, $request->validated());
            DB::commit();

            return $this->responseSuccess($data, 'Behavior has been update successfully.');
        } catch (Exception $e) {
            DB::rollBack();

            return $this->responseError([], 'Data Process Error!');
        }
    }

    public function destroy(int $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = $this->service->delete($id);
            if (! $data) {
                return $this->responseError([], 'Data Not Found!', 204);
            }

            DB::commit();

            return $this->responseSuccess($data, 'Behavior has been delete successfully.', 200);
        } catch (Exception $e) {
            DB::rollBack();

            return $this->responseError([], 'Data Not Found!');
        }
    }
}
