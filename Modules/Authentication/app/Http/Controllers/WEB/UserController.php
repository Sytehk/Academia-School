<?php

namespace Modules\Authentication\Http\Controllers\WEB;

use App\Enums\UserLogAction;
use App\Http\Controllers\Controller;
use App\Traits\Trackable;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;
use Modules\Academic\Services\UserService;
use Modules\Authentication\DataTables\User\UserDataTable;
use Modules\Authentication\Http\Requests\UserStoreRequest;
use Modules\Authentication\Http\Requests\UserUpdateRequest;
use Modules\Authentication\Models\User;
use RealRashid\SweetAlert\Facades\Alert;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    use Trackable;

    public function __construct(
        private UserService $service
    ) {}

    public function index(UserDataTable $dataTable): mixed
    {
        return $dataTable->render('authentication::user.index');
    }

    public function create(): Factory|View
    {
        $roles = Role::where('created_by', getUserId())->get();

        return view('authentication::user.create', compact('roles'));
    }

    public function store(UserStoreRequest $request): Redirector|RedirectResponse
    {
        DB::beginTransaction();
        try {
            $user = $this->service->createOrUpdateUser($request->validated());

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

            if ($request->role_id && $user) {
                $role = Role::where('id', (int) $request->role_id)->first();
                $user->assignRole($role);
            }

            $model_id2 = $user->id;
            $detail2 = 'User create';
            $this->trackAction(UserLogAction::CREATE, User::class, $model_id2, $detail2);

            DB::commit();

            Alert::toast('User has been create successfully.', 'success');

            return redirect('users');
        } catch (\Exception $e) {
            DB::rollBack();

            Alert::error('Error', $e->getMessage());

            return redirect('users');
        }
    }

    public function edit(int $id): Factory|View
    {
        $roles = Role::all();
        $user = $this->service->findUserById($id);

        return view('authentication::user.edit', compact('user', 'roles'));
    }

    public function update(UserUpdateRequest $request, int $id): Redirector|RedirectResponse
    {
        DB::beginTransaction();
        try {
            $user = $this->service->createOrUpdateUser($request->validated(), $id);

            // Delete roles.
            $user->roles()->detach();

            if ($request->role && $user) {
                $role = Role::where('id', (int) $request->role_id)->first();
                $user->assignRole($role);
            }

            DB::commit();
            Alert::toast('User has been update successfully.', 'success');

            return redirect('users');
        } catch (\Exception $e) {
            DB::rollBack();

            Alert::error('Error', $e->getMessage());

            return redirect('users');
        }
    }

    public function destroy(int $id): Redirector|RedirectResponse
    {
        DB::beginTransaction();
        try {
            $user = $this->service->findUserById($id);

            // Delete roles.
            $user->roles()->detach();

            $this->service->deleteUserById($id);

            DB::commit();
            Alert::toast('User has been delete successfully.', 'success');

            return redirect('users');
        } catch (\Exception $e) {
            DB::rollBack();
            Alert::error('Error', $e->getMessage());

            return redirect('users');
        }
    }
}
