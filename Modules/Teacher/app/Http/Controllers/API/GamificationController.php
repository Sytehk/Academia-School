<?php

namespace Modules\Teacher\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Teacher\Http\Requests\Gamification\GamificationStoreRequest;
use Modules\Teacher\Http\Requests\Gamification\GamificationUpdateRequest;
use Modules\Teacher\Services\Gamification\GamificationService;

class GamificationController extends Controller
{
    public function __construct(
        private GamificationService $service
    ) {}

    public function all(Request $request): JsonResponse
    {
        $gamifications = $this->service->getAll($request);

        if (! $gamifications) {
            return $this->responseError([], 'No Data Found!');
        }

        return $this->responseSuccess($gamifications, 'Gamification has been fetch successfully.');
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = request('per_page') ?? 10;
        $gamifications = $this->service->index($request, $perPage);
        if (! $gamifications) {
            return $this->responseError([], 'No Data Found!');
        }

        return $this->responseSuccess($gamifications, 'Gamification has been fetch successfully.');
    }

    public function store(GamificationStoreRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = $this->service->create($request->validated());
            DB::commit();

            return $this->responseSuccess($data, 'Gamification has been create successfully.');
        } catch (Exception $e) {
            DB::rollBack();

            return $this->responseError([], 'Data Process Error!');
        }
    }

    public function show(int $id): JsonResponse
    {
        $gamification = $this->service->getById($id);
        if (! $gamification) {
            return $this->responseError([], 'No Data Found!');
        }

        return $this->responseSuccess($gamification, 'Gamification has been show successfully.');
    }

    public function update(GamificationUpdateRequest $request, int $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = $this->service->update($id, $request->validated());
            DB::commit();

            return $this->responseSuccess($data, 'Gamification has been update successfully.');
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

            return $this->responseSuccess($data, 'Gamification has been delete successfully.', 200);
        } catch (Exception $e) {
            DB::rollBack();

            return $this->responseError([], 'Data Not Found!');
        }
    }
}
