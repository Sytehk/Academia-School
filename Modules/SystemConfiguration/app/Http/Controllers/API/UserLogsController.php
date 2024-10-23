<?php

namespace Modules\SystemConfiguration\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\SystemConfiguration\Models\UserLog;
use Modules\SystemConfiguration\Services\UserLogService;

class UserLogsController extends Controller
{
    public function __construct(private readonly UserLogService $userLogService) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->per_page ?: 10;

        $query = UserLog::query()
            ->where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->with('user');

        // 👇 Role Check
        if (auth()->user()->role_id != 2) {
            $query->where('user_id', getUserId());
        }

        $userLogs = $query->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return $this->responseSuccess($userLogs, 'User activities fetched successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        if (auth()->user()->role_id != 1) {
            return $this->responseError([], _lang('Unauthorized access.'), 403);
        }

        $userLogs = $this->userLogService->deleteUserLogById($id);

        if (! $userLogs) {
            return $this->responseError([], _lang('Failed to delete records unsuccessfully'));
        }

        return $this->responseSuccess(
            [],
            _lang('User activities delete successfully.')
        );
    }
}
