<?php

namespace Modules\Authentication\Http\Controllers\API;

use App\Enums\UserLogAction;
use App\Http\Controllers\Controller;
use App\Traits\Trackable;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;
use Modules\Academic\Services\UserService;
use Modules\Authentication\Http\Requests\UserStoreRequest;
use Modules\Authentication\Http\Requests\UserUpdateRequest;
use Modules\Authentication\Models\User;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    use Trackable;

    public function __construct(
        private UserService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->per_page ?: 10;

        try {
            $query = User::query()
                ->where('institute_id', get_institute_id())
                ->where('branch_id', get_branch_id())
                ->orderByDesc('id');

            $users = $query->paginate($perPage);

            return $this->responseSuccess(
                $users,
                _lang('Users has been fetched successfully.')
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function store(UserStoreRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $user = $this->service->createOrUpdateUser($request->validated(), null, $request);

            $agent = new Agent;
            $device_info = [
                'device' => $agent->device(),
                'platform' => $agent->platform(),
                'platform_version' => $agent->version($agent->platform()),
                'browser' => $agent->browser(),
                'browser_version' => $agent->version($agent->browser()),
            ];

            $user->update([
                'email_verified_at' => Carbon::now(),
                'username' => Str::slug($request->name),
                'platform' => 'WEB',
                'device_info' => $device_info,
            ]);

            if ($request->role && $user) {
                $role = Role::where('id', (int) $request->role_id)->first();
                $user->assignRole($role);
                $user->update([
                    'user_type' => $role->name,
                ]);
            }

            $model_id2 = $user->id;
            $detail2 = 'User create';
            $this->trackAction(UserLogAction::CREATE, User::class, $model_id2, $detail2);

            DB::commit();

            return $this->responseSuccess(
                $user,
                _lang('User has been create successfully.')
            );
        } catch (Exception $exception) {
            DB::rollBack();

            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function show(int $id): JsonResponse
    {
        $user = $this->service->findUserById($id);

        return $this->responseSuccess(
            $user,
            _lang('User has been create successfully.')
        );
    }

    public function update(UserUpdateRequest $request, int $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $user = $this->service->createOrUpdateUser($request->validated(), $id, $request);

            // Delete roles.
            $user->roles()->detach();

            if ($request->role && $user) {
                $role = Role::where('id', (int) $request->role_id)->first();
                $user->assignRole($role);
            }

            DB::commit();

            return $this->responseSuccess(
                $user,
                _lang('User has been update successfully.')
            );
        } catch (Exception $exception) {
            DB::rollBack();

            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $user = $this->service->findUserById($id);

            // Delete roles.
            $user->roles()->detach();

            $this->service->deleteUserById($id);

            DB::commit();

            return $this->responseSuccess(
                [],
                _lang('User has been delete successfully.')
            );
        } catch (Exception $exception) {
            DB::rollBack();

            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }
}
