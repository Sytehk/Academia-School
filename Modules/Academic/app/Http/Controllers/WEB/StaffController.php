<?php

namespace Modules\Academic\Http\Controllers\WEB;

use App\Enums\UserLogAction;
use App\Http\Controllers\Controller;
use App\Traits\Trackable;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Modules\Academic\Http\Requests\StaffCreateRequest;
use Modules\Academic\Http\Requests\StaffUpdateRequest;
use Modules\Academic\Models\Department;
use Modules\Academic\Models\Staff;
use Modules\Academic\Services\StaffService;
use Modules\Academic\Services\UserService;
use Modules\Authentication\Models\User;
use RealRashid\SweetAlert\Facades\Alert;

class StaffController extends Controller
{
    use Trackable;

    public function __construct(
        private readonly UserService $userService,
        private readonly StaffService $staffService,
    ) {}

    public function index(Request $request): View|Factory
    {
        $perPage = (int) $request->per_page ?: 10;

        $staffs = Staff::select(
            'staffs.id AS id',
            'users.id as user_id',
            'users.name',
            'users.email',
            'users.phone as user_phone',
            'users.image',
            'users.role_id',
            'users.status',
            'users.user_status',
            'users.user_type',
            'staffs.designation',
            'staffs.birthday',
            'staffs.gender',
            'staffs.religion',
            'staffs.sl',
            'staffs.blood',
            'staffs.address',
            'staffs.status AS staff_status',
            'staffs.is_administrator'
        )
            ->join('users', 'users.id', '=', 'staffs.user_id')
            ->where('staffs.institute_id', get_institute_id())
            ->where('staffs.branch_id', get_branch_id())
            ->whereNotIn('users.user_type', ['Admin'])
            ->orderBy('staffs.id', 'DESC')
            ->paginate($perPage);

        return view('academic::staffs.staff-list', compact('staffs'));
    }

    public function create(): Factory|View
    {
        $roles = DB::table('roles')
            ->where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->get();

        return view('academic::staffs.staff-add', [
            'roles' => $roles,
            'bloods' => get_blood_groups(),
            'departments' => Department::select('id', 'department_name')->get(),
        ]);
    }

    public function store(StaffCreateRequest $request): Redirector|RedirectResponse
    {
        $imagePath = null;

        try {
            DB::beginTransaction();

            // ✅ role_id is already validated to exist (scoped to institute/branch) by StaffCreateRequest
            $role = DB::table('roles')->where('id', (int) $request->role_id)->first();

            if (! $role) {
                throw new Exception(_lang('Invalid role selected.'));
            }

            // ✅ Upload Image
            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $imagePath = fileUploader('users/', 'png', $request->file('image'));
            }

            // ✅ Create User
            $user = $this->userService->createOrUpdateUser([
                'institute_id' => get_institute_id(),
                'branch_id' => get_branch_id(),
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => $request->password,
                'role_id' => $role->id,
                'status' => 1,
                'image' => $imagePath,
                'user_type' => 'Staff',
            ]);

            if (! $user) {
                throw new Exception(_lang('Staff login account could not be created.'));
            }

            $this->trackAction(UserLogAction::CREATE, User::class, $user->id, 'User Create');

            // ✅ Create Staff
            $staff = $this->staffService->createStaff([
                'user_id' => $user->id,
                'name' => $request->name,
                'designation' => $request->designation,
                'department_id' => $request->department_id,
                'birthday' => $request->birthday,
                'gender' => $request->gender,
                'religion' => $request->religion,
                'phone' => $request->phone,
                'blood' => $request->blood,
                'sl' => $request->sl,
                'address' => $request->address,
                'joining_date' => $request->joining_date,
                'access_key' => $request->password,
            ]);

            if (! $staff) {
                throw new Exception(_lang('Staff record could not be created.'));
            }

            $this->trackAction(UserLogAction::CREATE, Staff::class, $staff->id, 'Staff Create');

            DB::commit();

            Alert::toast(_lang('Staff created successfully.'), 'success');

            return redirect('staffs');
        } catch (Exception $e) {
            DB::rollback();

            if ($imagePath) {
                fileRemover('users/', $imagePath);
            }

            Log::error('Staff store failed: '.$e->getMessage(), ['exception' => $e]);

            Alert::error('Error', $e->getMessage());

            return back()->withInput();
        }
    }

    public function show(int $id): Factory|View|RedirectResponse
    {
        // department_id is nullable, so this must be a leftJoin — an inner join
        // silently drops any staff member without a department.
        $staff = Staff::select(
            '*',
            'staffs.id AS id',
            'users.phone as user_phone',
            'departments.department_name'
        )
            ->join('users', 'users.id', '=', 'staffs.user_id')
            ->leftJoin('departments', 'departments.id', '=', 'staffs.department_id')
            ->where('staffs.institute_id', get_institute_id())
            ->where('staffs.branch_id', get_branch_id())
            ->where('staffs.id', intval($id))
            ->first();

        if (! $staff) {
            Alert::error('Error', _lang('Staff not found.'));

            return redirect('staffs');
        }

        return view('academic::staffs.staff-view', compact('staff'));
    }

    public function edit(int $id): Factory|Redirector|RedirectResponse|View
    {
        $roles = DB::table('roles')
            ->where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->get();

        // department_id is nullable, so this must be a leftJoin — an inner join
        // would make the edit page report "already deleted" for a staff member
        // who simply has no department assigned.
        $staff = Staff::select(
            '*',
            'staffs.id AS id',
            'users.phone as user_phone',
            'departments.department_name'
        )
            ->join('users', 'users.id', '=', 'staffs.user_id')
            ->leftJoin('departments', 'departments.id', '=', 'staffs.department_id')
            ->where('staffs.institute_id', get_institute_id())
            ->where('staffs.branch_id', get_branch_id())
            ->where('staffs.id', intval($id))
            ->first();

        if (! $staff) {
            Alert::error('Error', _lang('Staff already deleted.'));

            return redirect('staffs');
        }

        return view('academic::staffs.staff-edit', [
            'roles' => $roles,
            'staff' => $staff,
            'bloods' => get_blood_groups(),
            'departments' => Department::select('id', 'department_name')->get(),
        ]);
    }

    public function update(StaffUpdateRequest $request, int $id): Redirector|RedirectResponse
    {
        $staff = $this->staffService->findStaffById($id);

        if (! $staff) {
            Alert::error('Error', _lang('Staff not found.'));

            return redirect('staffs');
        }

        $newImagePath = null;

        try {
            DB::beginTransaction();

            // ✅ role_id is already validated to exist (scoped to institute/branch) by StaffUpdateRequest
            $role = DB::table('roles')->where('id', (int) $request->role_id)->first();

            if (! $role) {
                throw new Exception(_lang('Invalid role selected.'));
            }

            // ✅ Update Staff
            $staff->update([
                'name' => $request->name,
                'designation' => $request->designation,
                'department_id' => $request->department_id,
                'birthday' => $request->birthday,
                'gender' => $request->gender,
                'religion' => $request->religion,
                'phone' => $request->phone,
                'blood' => $request->blood,
                'sl' => $request->sl,
                'address' => $request->address,
                'joining_date' => $request->joining_date,
                'access_key' => $request->filled('password') ? $request->password : $staff->access_key,
            ]);

            $this->trackAction(UserLogAction::UPDATE, Staff::class, $staff->id, 'Staff Update');

            // ✅ Update User
            $user = $this->userService->findUserById((int) $staff->user_id);

            if (! $user) {
                throw new Exception(_lang('Linked login account not found for this staff member.'));
            }

            $imagePath = $user->image;
            if (! empty($request->image) && $request->image instanceof UploadedFile) {
                $imagePath = fileUploader('users/', 'png', $request->image, $user->image);
                $newImagePath = $imagePath;
            }

            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->role_id = $role->id;
            $user->image = $imagePath;

            // ✅ Password update ONLY if provided
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            $this->trackAction(UserLogAction::UPDATE, User::class, $user->id, 'User Update');

            DB::commit();

            Alert::toast(_lang('Staff updated successfully.'), 'success');

            return redirect('staffs');
        } catch (Exception $e) {
            DB::rollback();

            if ($newImagePath) {
                fileRemover('users/', $newImagePath);
            }

            Log::error('Staff update failed: '.$e->getMessage(), ['staff_id' => $id, 'exception' => $e]);

            Alert::error('Error', $e->getMessage());

            return back()->withInput();
        }
    }

    public function updateStaffStatus(Request $request): Redirector|RedirectResponse
    {
        $request->validate([
            'id' => 'required|integer',
            'status' => 'required|in:0,1',
        ]);

        $user = User::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->find((int) $request->id);

        if (! $user) {
            Alert::error('Error', _lang('User already deleted.'));

            return redirect('staffs');
        }

        $user->status = $request->status;
        $user->save();

        Alert::toast(_lang('Staff Information has been updated'), 'success');

        return redirect('staffs');
    }

    public function destroy(int $id): Redirector|RedirectResponse
    {
        try {
            DB::transaction(function () use ($id) {
                $staff = $this->staffService->findStaffById($id);

                if (! $staff) {
                    throw new Exception(_lang('Staff not found.'));
                }

                $user = $this->userService->findUserById((int) $staff->user_id);

                $this->trackAction(UserLogAction::DELETE, Staff::class, $staff->id, 'staff delete');

                if ($user) {
                    $this->trackAction(UserLogAction::DELETE, User::class, $user->id, 'user delete');
                    $this->userService->deleteUserById((int) $user->id);
                }

                $this->staffService->deleteStaffById((int) $staff->id);
            });

            Alert::toast(_lang('Staff Information has been deleted'), 'success');
        } catch (Exception $e) {
            Log::error('Staff delete failed: '.$e->getMessage(), ['staff_id' => $id]);
            Alert::error('Error', $e->getMessage());
        }

        return redirect('staffs');
    }

    public function staffMultipleDelete(Request $request): Redirector|RedirectResponse
    {
        $request->validate([
            'staff_ids' => 'required|array|min:1',
            'staff_ids.*' => 'integer|exists:staffs,id',
        ]);

        $deleted = 0;
        $failed = 0;

        foreach ($request->staff_ids as $staffId) {
            try {
                DB::transaction(function () use ($staffId) {
                    $staff = $this->staffService->findStaffById((int) $staffId);

                    if (! $staff) {
                        throw new Exception(_lang('Staff not found.'));
                    }

                    $this->trackAction(UserLogAction::DELETE, Staff::class, $staff->id, 'Staff Delete (Multiple)');

                    if ($staff->user_id) {
                        $user = $this->userService->findUserById((int) $staff->user_id);

                        if ($user) {
                            $this->trackAction(UserLogAction::DELETE, User::class, $user->id, 'User Credentials Delete (Multiple)');
                            $this->userService->deleteUserById((int) $user->id);
                        }
                    }

                    $this->staffService->deleteStaffById((int) $staff->id);
                });

                $deleted++;
            } catch (Exception $e) {
                $failed++;
                Log::error('Bulk staff delete failed: '.$e->getMessage(), ['staff_id' => $staffId]);
            }
        }

        if ($failed > 0) {
            Alert::error('Error', "{$deleted} "._lang('deleted').", {$failed} "._lang('failed').'. '._lang('Check logs for details.'));
        } else {
            Alert::toast(_lang('Staffs data has been deleted.'), 'success');
        }

        return redirect('staffs');
    }
}
