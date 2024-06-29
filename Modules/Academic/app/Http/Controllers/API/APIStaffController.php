<?php

namespace Modules\Academic\Http\Controllers\API;

use App\Enums\UserLogAction;
use App\Http\Controllers\Controller;
use App\Traits\Trackable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Modules\Academic\Http\Requests\StaffCreateRequest;
use Modules\Academic\Http\Requests\StaffUpdateRequest;
use Modules\Academic\Models\Staff;
use Modules\Authentication\Models\User;

class APIStaffController extends Controller
{
    use Trackable;

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->per_page ?: 10;

        $staffs = Staff::select(
            'staffs.id AS id',
            'users.id as user_id',
            'users.name',
            'users.email',
            'users.phone',
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
            ->orderBy('staffs.id', 'DESC')
            ->paginate($perPage);

        return $this->responseSuccess($staffs, 'Staffs have been fetched successfully.');
    }

    public function store(StaffCreateRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $imagePath = fileUploader('users/', 'png', $request->file('image'));
            }

            $role = DB::table('roles')
                ->where('institute_id', get_institute_id())
                ->where('branch_id', get_branch_id())
                ->where('id', (int) $request->role_id)
                ->first();
            // role not assign.

            $user = new User;
            $user->institute_id = get_institute_id();
            $user->branch_id = get_branch_id();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->role_id = $role->id;
            $user->user_type = $role->name;
            $user->phone = $request->phone;
            $user->image = $imagePath ?? null;
            $user->save();

            $model_id1 = $user->id;
            $detail2 = 'user create';
            $this->trackAction(UserLogAction::CREATE, User::class, $model_id1, $detail2);

            $staff = new Staff;
            $staff->institute_id = get_institute_id();
            $staff->branch_id = get_branch_id();
            $staff->user_id = $user->id;
            $staff->name = $request->name;
            $staff->designation = $request->designation;
            $staff->department_id = $request->department_id;
            $staff->birthday = $request->birthday;
            $staff->gender = $request->gender;
            $staff->religion = $request->religion;
            $staff->phone = $request->phone;
            $staff->blood = $request->blood;
            $staff->sl = $request->sl;
            $staff->address = $request->address;
            $staff->joining_date = $request->joining_date;
            $staff->access_key = $request->password;
            $staff->save();

            $model_id2 = $staff->id;
            $detail2 = 'staff create';
            $this->trackAction(UserLogAction::CREATE, Staff::class, $model_id2, $detail2);

            DB::commit();

            return $this->responseSuccess($staff, 'Staff have been create successfully.');
        } catch (\Exception $e) {
            DB::rollback();

            return $this->responseError([], $e->getMessage());
        }
    }

    public function show(int $id): JsonResponse
    {
        $staff = Staff::select(
            'staffs.id AS id',
            'users.id as user_id',
            'users.name',
            'users.email',
            'users.phone',
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
            ->where('staffs.id', intval($id))
            ->first();

        if (! $staff) {
            return $this->responseError([], _lang('Staff not found.'), 404);
        }

        return $this->responseSuccess($staff, 'Staff have been fetched successfully.');
    }

    public function update(StaffUpdateRequest $request, int $id): JsonResponse
    {
        $staff = Staff::find($id);
        if (! $staff) {
            return $this->responseError([], _lang('Staff not found.'), 404);
        }

        $request->validate([
            'email' => [
                'nullable',
                Rule::unique('users', 'email')->ignore($staff->user_id, 'id'),
            ],
        ]);

        DB::beginTransaction();
        try {
            $staff->name = $request->name;
            $staff->designation = $request->designation;
            $staff->department_id = $request->department_id;
            $staff->birthday = $request->birthday;
            $staff->gender = $request->gender;
            $staff->religion = $request->religion;
            $staff->phone = $request->phone;
            $staff->blood = $request->blood;
            $staff->sl = $request->sl;
            $staff->address = $request->address;
            $staff->joining_date = $request->joining_date;
            if ($request->password) {
                $staff->access_key = $request->password;
            }
            $staff->save();

            $model_id1 = $staff->id;
            $detail1 = 'staff update';
            $this->trackAction(UserLogAction::UPDATE, Staff::class, $model_id1, $detail1);

            $role = DB::table('roles')
                ->where('institute_id', get_institute_id())
                ->where('branch_id', get_branch_id())
                ->where('id', (int) $request->role_id)
                ->first();
            $user = User::find($staff->user_id);
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->role_id = $role->id;
            $user->user_type = $role->name;
            if ($request->password) {
                $user->password = Hash::make($request->password);
            }
            $imagePath = null;
            if (! empty($request['image']) && $request['image'] instanceof UploadedFile) {
                $imagePath = fileUploader('users/', 'png', $request['image'], $staff->user?->image);
                $user->image = $imagePath;
            }
            $user->save();

            $model_id2 = $user->id;
            $detail2 = 'User update';
            $this->trackAction(UserLogAction::UPDATE, User::class, $model_id2, $detail2);

            DB::commit();

            return $this->responseSuccess($staff, 'Staff have been update successfully.');
        } catch (\Exception $e) {
            DB::rollback();

            return $this->responseError([], $e->getMessage());
        }
    }

    public function updateStaffStatus(Request $request): JsonResponse
    {
        $userId = intval($request->id);

        $user = User::find($userId);
        if (! $user) {
            return $this->responseError([], _lang('User not found.'), 404);
        }

        $user->status = $request->status;
        $user->save();

        return $this->responseSuccess([], 'Staff have been update successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        // Find staff by ID
        $staff = Staff::find($id);

        // If staff exists, find its associated user
        $user = $staff ? User::find($staff->user_id) : null;

        // Check if either staff or user exists
        if (! $staff && ! $user) {
            return $this->responseError([], _lang('User or Staff not found.'), 404);
        }

        // Log staff deletion if it exists
        if ($staff) {
            $this->trackAction(UserLogAction::DELETE, Staff::class, $staff->id, 'staff delete');
        }

        // Log user deletion if it exists
        if ($user) {
            $this->trackAction(UserLogAction::DELETE, User::class, $user->id, 'user delete');
        }

        // Delete user if exists
        if ($user) {
            $user->delete();
        }

        // Delete staff if exists
        if ($staff) {
            $staff->delete();
        }

        return $this->responseSuccess([], 'Staff has been deleted successfully.');
    }

    public function staffMultipleDelete(Request $request): JsonResponse
    {
        $request->validate([
            'staff_ids' => 'required|array',
            'staff_ids.*' => 'exists:staff,id', // Ensure each ID exists
        ]);

        $deletedStaff = [];
        $deletedUsers = [];

        foreach ($request->staff_ids as $staff_id) {
            $staff = Staff::find($staff_id);

            if ($staff) {
                // Find associated user
                $user = $staff->user_id ? User::find($staff->user_id) : null;

                // Log staff deletion
                $this->trackAction(UserLogAction::DELETE, Staff::class, $staff->id, 'Staff Deleted');

                // Log user deletion (if exists)
                if ($user) {
                    $this->trackAction(UserLogAction::DELETE, User::class, $user->id, 'User Credentials Deleted');
                    $deletedUsers[] = $user->id;
                    $user->delete();
                }

                $deletedStaff[] = $staff->id;
                $staff->delete();
            }
        }

        return $this->responseSuccess([
            'deleted_staff' => $deletedStaff,
            'deleted_users' => $deletedUsers,
        ], 'Staff members have been deleted successfully.');
    }
}
