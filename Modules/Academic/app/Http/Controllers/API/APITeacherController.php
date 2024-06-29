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
use Modules\Academic\Models\Teacher;
use Modules\Academic\Services\TeacherService;
use Modules\Academic\Services\UserService;
use Modules\Authentication\Models\User;

class APITeacherController extends Controller
{
    use Trackable;

    public function __construct(
        private readonly UserService $userService,
        private readonly TeacherService $teacherService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->per_page ?: 10;

        $teachers = Teacher::select(
            'teachers.id AS id',
            'users.id as user_id',
            'users.name',
            'users.email',
            'users.phone',
            'users.image',
            'users.role_id',
            'users.status',
            'users.user_status',
            'users.user_type',
            'teachers.designation',
            'teachers.birthday',
            'teachers.gender',
            'teachers.religion',
            'teachers.sl',
            'teachers.blood',
            'teachers.address',
            'teachers.status AS teacher_status',
            'teachers.is_administrator'
        )
            ->join('users', 'users.id', '=', 'teachers.user_id')
            ->orderBy('teachers.id', 'DESC')->paginate($perPage);

        return $this->responseSuccess($teachers, 'Teacher have been fetched successfully.');
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'phone' => 'required|numeric|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'designation' => 'nullable|string|max:50',
            'birthday' => 'nullable',
            'gender' => 'nullable|string|max:50',
            'religion' => 'nullable|string|max:50',
            'sl' => 'nullable|integer',
            'role_id' => 'required|integer',
            'blood' => 'nullable|string',
            'address' => 'nullable',
            'email' => 'nullable|string|max:50|unique:users',
            'image' => 'nullable|image|max:5120',
        ]);

        $imagePath = null;
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $imagePath = fileUploader('users/', 'png', $request->file('image'));
        }

        DB::beginTransaction();
        try {
            $role = DB::table('roles')
                ->where('institute_id', get_institute_id())
                ->where('branch_id', get_branch_id())
                ->where('id', (int) $request->role_id)
                ->first();

            $user = new User;
            $user->institute_id = get_institute_id();
            $user->branch_id = get_branch_id();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->role_id = $role->id;
            $user->user_type = $role->name;
            $user->phone = $request->phone;
            $user->image = $imagePath;
            $user->save();

            $userId = $user->id;
            $detail1 = 'User Create';
            $this->trackAction(UserLogAction::CREATE, User::class, $userId, $detail1);

            $teacher = new Teacher;
            $teacher->institute_id = get_institute_id();
            $teacher->branch_id = get_branch_id();
            $teacher->user_id = $user->id;
            $teacher->name = $request->name;
            $teacher->designation = $request->designation;
            $teacher->department_id = $request->department_id;
            $teacher->birthday = $request->birthday;
            $teacher->gender = $request->gender;
            $teacher->religion = $request->religion;
            $teacher->phone = $request->phone;
            $teacher->blood = $request->blood;
            $teacher->sl = $request->sl;
            $teacher->address = $request->address;
            $teacher->joining_date = $request->joining_date;
            $teacher->is_administrator = $request->is_administrator ?? '0';
            $teacher->access_key = $request->password;
            $teacher->save();

            $teacherId = $teacher->id;
            $detail2 = 'Teacher Create';
            $this->trackAction(UserLogAction::CREATE, Teacher::class, $teacherId, $detail2);

            DB::commit();

            return $this->responseSuccess($teacher, 'Teacher have been create successfully.');
        } catch (\Exception $e) {
            DB::rollback();

            return $this->responseError([], $e->getMessage());
        }
    }

    public function show(int $id): JsonResponse
    {
        $teacher = Teacher::select(
            'teachers.id AS id',
            'users.id as user_id',
            'users.name',
            'users.email',
            'users.phone',
            'users.image',
            'users.role_id',
            'users.status',
            'users.user_status',
            'users.user_type',
            'teachers.designation',
            'teachers.birthday',
            'teachers.gender',
            'teachers.religion',
            'teachers.sl',
            'teachers.blood',
            'teachers.address',
            'teachers.status AS teacher_status',
            'teachers.is_administrator'
        )
            ->join('users', 'users.id', '=', 'teachers.user_id')
            ->where('teachers.id', intval($id))
            ->first();

        if (! $teacher) {
            return $this->responseError([], _lang('Teacher not found.'), 404);
        }

        return $this->responseSuccess($teacher, 'Teacher have been fetched successfully.');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $teacher = Teacher::find($id);
        if (! $teacher) {
            return $this->responseError([], _lang('Teacher not found.'), 404);
        }

        $request->validate([
            'name' => 'required|string|max:191',
            'designation' => 'nullable|string|max:191',
            'birthday' => 'nullable',
            'gender' => 'nullable|string|max:191',
            'religion' => 'nullable|string|max:191',
            'phone' => 'nullable|string|max:191',
            'address' => 'nullable|string|max:191',
            'sl' => 'nullable|integer',
            'blood' => 'nullable|string',
            'role_id' => 'required|integer',
            'email' => [
                'required',
                Rule::unique('users', 'email')->ignore($teacher->user_id, 'id'),
            ],
            'password' => 'nullable|min:6|confirmed',
            'image' => 'nullable|image|max:5120',
        ]);

        try {
            $teacher->name = $request->name;
            $teacher->designation = $request->designation;
            $teacher->department_id = $request->department_id;
            $teacher->birthday = $request->birthday;
            $teacher->gender = $request->gender;
            $teacher->religion = $request->religion;
            $teacher->phone = $request->phone;
            $teacher->blood = $request->blood;
            $teacher->sl = $request->sl;
            $teacher->address = $request->address;
            $teacher->joining_date = $request->joining_date;
            $teacher->is_administrator = $request->is_administrator ?? '0';
            if ($request->password) {
                $teacher->access_key = $request->password;
                $teacher->save();
            }

            $model_id1 = $teacher->id;
            $detail1 = 'Teacher Update';
            $this->trackAction(UserLogAction::UPDATE, Teacher::class, $model_id1, $detail1);

            $role = DB::table('roles')
                ->where('institute_id', get_institute_id())
                ->where('branch_id', get_branch_id())
                ->where('id', (int) $request->role_id)
                ->first();
            $user = User::find($teacher->user_id);
            if ($user) {
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
                    $imagePath = fileUploader('users/', 'png', $request['image'], $teacher->user?->image);
                    $user->image = $imagePath;
                }

                $user->save();
                $model_id2 = $user->id;
                $detail2 = 'User Update';
                $this->trackAction(UserLogAction::UPDATE, User::class, $model_id2, $detail2);
            }

            return $this->responseSuccess($teacher, 'Teacher have been update successfully.');
        } catch (\Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function updateTeacherStatus(Request $request): JsonResponse
    {
        $userId = intval($request->id);

        $user = User::find($userId);
        if (! $user) {
            return $this->responseError([], _lang('User not found.'), 404);
        }

        $user->status = $request->status;
        $user->save();

        return $this->responseSuccess([], 'Teacher have been create successfully.');
    }

    public function destroy(int $id): JsonResponse
    {
        // Find teacher by ID
        $teacher = Teacher::find($id);
        if (! $teacher) {
            return $this->responseError([], _lang('Teacher not found.'), 404);
        }

        // Find associated user
        $user = $teacher->user_id ? User::find($teacher->user_id) : null;
        if (! $user) {
            return $this->responseError([], _lang('User not found.'), 404);
        }

        // Log teacher deletion
        $this->trackAction(UserLogAction::DELETE, Teacher::class, $teacher->id, 'Teacher Deleted');

        // Log user deletion
        $this->trackAction(UserLogAction::DELETE, User::class, $user->id, 'User Credentials Deleted');

        // Delete user and teacher
        $user->delete();
        $teacher->delete();

        return $this->responseSuccess([], 'Teacher has been deleted successfully.');
    }

    public function statusEnable(int $id): JsonResponse
    {
        $teacher = $this->teacherService->findTeacherById((int) $id);
        if (! $teacher) {
            return $this->responseError([], _lang('Teacher not found.'), 404);
        }

        $teacher->status = '1';
        $teacher->save();

        $user = $this->userService->findUserById((int) $teacher->user_id);
        $user->user_status = '1';
        $user->save();

        return $this->responseSuccess([], 'Teacher have been enable successfully.');
    }

    public function statusDisable(int $id): JsonResponse
    {
        $teacher = $this->teacherService->findTeacherById((int) $id);
        if (! $teacher) {
            return $this->responseError([], _lang('Teacher not found.'), 404);
        }

        $teacher->status = '0';
        $teacher->save();

        $user = $this->userService->findUserById((int) $teacher->user_id);
        $user->user_status = '0';
        $user->save();

        return $this->responseSuccess([], 'Teacher have been disable successfully.');
    }

    public function teacherMultipleDelete(Request $request): JsonResponse
    {
        $request->validate([
            'teacher_ids' => 'required|array',
            'teacher_ids.*' => 'exists:teachers,id', // Ensure each ID exists
        ]);

        $deletedTeachers = [];
        $deletedUsers = [];

        foreach ($request->teacher_ids as $teacher_id) {
            $teacher = Teacher::find($teacher_id);

            if ($teacher) {
                // Find associated user
                $user = $teacher->user_id ? User::find($teacher->user_id) : null;

                // Log teacher deletion
                $this->trackAction(UserLogAction::DELETE, Teacher::class, $teacher->id, 'Teacher Deleted');

                // Log user deletion (if exists)
                if ($user) {
                    $this->trackAction(UserLogAction::DELETE, User::class, $user->id, 'User Credentials Deleted');
                    $deletedUsers[] = $user->id;
                    $user->delete();
                }

                $deletedTeachers[] = $teacher->id;
                $teacher->delete();
            }
        }

        return $this->responseSuccess([
            'deleted_teachers' => $deletedTeachers,
            'deleted_users' => $deletedUsers,
        ], 'Teachers have been deleted successfully.');
    }
}
