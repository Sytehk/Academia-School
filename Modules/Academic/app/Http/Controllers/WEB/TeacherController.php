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
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Modules\Academic\Models\Department;
use Modules\Academic\Models\Teacher;
use Modules\Academic\Services\TeacherService;
use Modules\Academic\Services\UserService;
use Modules\Authentication\Models\User;
use RealRashid\SweetAlert\Facades\Alert;

class TeacherController extends Controller
{
    use Trackable;

    public function __construct(
        private readonly UserService $userService,
        private readonly TeacherService $teacherService,
    ) {}

    public function index(Request $request): Factory|View
    {
        // Unpaginated ->get() previously loaded and rendered EVERY teacher in
        // the branch on every visit, relying entirely on client-side DataTables
        // to make that navigable. Paginated server-side now, matching
        // StaffController::index() — see teacher-list.blade.php for the
        // matching data-server-paginated + pagination-links change; without
        // both, this alone would silently cap the visible list at one page
        // with no way to reach the rest, which is worse than today's slow but
        // complete list.
        $perPage = (int) $request->per_page ?: 20;

        // department_id is nullable, so this must be a leftJoin — an inner join
        // silently drops any teacher without a department from the whole list.
        //
        // A bare '*' here (as it was) expands to every column from teachers,
        // users AND departments, all joined — same-named columns collide, and
        // PDO keeps only the last one processed. Whichever value survived (e.g.
        // 'name', which both teachers and users carry) depended on physical join
        // order rather than being chosen on purpose, so adding a column to either
        // table later could silently change what the page shows. Scoped to
        // 'teachers.*' plus the specific columns the view actually needs,
        // preserving today's values explicitly instead of by accident: teacher
        // create/update always write $request->name to both tables in the same
        // request, so 'users.name' and 'teachers.name' are kept identical --
        // keeping 'users.name' here changes nothing observable.
        $teachers = Teacher::select(
            'teachers.*',
            'teachers.id AS id',
            'teachers.name AS teacher_name',
            'teachers.status AS teacher_status',
            'users.name as name',
            'users.image as image',
            'users.phone as user_phone',
            'departments.department_name'
        )
            ->where('teachers.institute_id', get_institute_id())
            ->where('teachers.branch_id', get_branch_id())
            ->join('users', 'users.id', '=', 'teachers.user_id')
            ->leftJoin('departments', 'departments.id', '=', 'teachers.department_id')
            ->orderBy('teachers.id', 'DESC')
            ->paginate($perPage);

        return view('academic::teachers.teacher-list', compact('teachers'));
    }

    public function create(): Factory|View
    {
        return view('academic::teachers.teacher-add', [
            'bloods' => get_blood_groups(),
            'departments' => Department::select('id', 'department_name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $instituteId = get_institute_id();
        $branchId = get_branch_id();

        $request->validate([
            'name' => 'required|string|max:50',
            'phone' => [
                'required',
                'string',
                'regex:/^[0-9+\-\s()]{7,20}$/',
                Rule::unique('users', 'phone')->where(fn ($q) => $q->where('institute_id', $instituteId)),
            ],
            'password' => 'required|string|min:6|confirmed',
            'designation' => 'nullable|string|max:50',
            'department_id' => [
                'nullable',
                Rule::exists('departments', 'id')->where(fn ($q) => $q->where('institute_id', $instituteId)->where('branch_id', $branchId)),
            ],
            'birthday' => 'nullable|date|before:today',
            'gender' => 'nullable|string|max:50',
            'religion' => 'nullable|string|max:50',
            'sl' => 'nullable|integer',
            'blood' => 'nullable|string',
            'address' => 'nullable|string',
            'email' => [
                'nullable',
                'email',
                'max:50',
                Rule::unique('users', 'email')->where(fn ($q) => $q->where('institute_id', $instituteId)),
            ],
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $imagePath = null;

        try {
            DB::beginTransaction();

            // ✅ IMAGE UPLOAD
            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $imagePath = fileUploader('users/', 'png', $request->file('image'));
            }

            // ✅ CREATE USER (via UserService so username generation/hashing is consistent everywhere)
            $user = $this->userService->createOrUpdateUser([
                'institute_id' => $instituteId,
                'branch_id' => $branchId,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => $request->password,
                'status' => 1,
                'image' => $imagePath,
                'user_type' => 'Teacher',
            ]);

            if (! $user) {
                throw new Exception(_lang('Teacher login account could not be created.'));
            }

            $this->trackAction(UserLogAction::CREATE, User::class, $user->id, 'User Created');

            // ✅ CREATE TEACHER
            $teacher = $this->teacherService->createTeacher([
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
                'access_key' => $request->password, // optional (plain, for admin reference)
            ]);

            if (! $teacher) {
                throw new Exception(_lang('Teacher record could not be created.'));
            }

            $this->trackAction(UserLogAction::CREATE, Teacher::class, $teacher->id, 'Teacher Created');

            DB::commit();

            Alert::success('Success', 'Teacher created successfully');

            return redirect()->route('teachers.index');
        } catch (Exception $e) {
            DB::rollback();

            if ($imagePath) {
                fileRemover('users/', $imagePath);
            }

            Log::error('Teacher store failed: '.$e->getMessage(), ['exception' => $e]);

            Alert::error('Error', $e->getMessage());

            return back()->withInput();
        }
    }

    public function show(int $id): Factory|View|RedirectResponse
    {
        $teacher = Teacher::select(
            '*',
            'teachers.id AS id',
        )
            ->join('users', 'users.id', '=', 'teachers.user_id')
            ->where('teachers.institute_id', get_institute_id())
            ->where('teachers.branch_id', get_branch_id())
            ->where('teachers.id', intval($id))
            ->first();

        if (! $teacher) {
            Alert::error('Error', _lang('Teacher not found.'));

            return redirect('teachers');
        }

        return view('academic::teachers.teacher-view', compact('teacher'));
    }

    public function edit(int $id): Factory|Redirector|RedirectResponse|View
    {
        // department_id is nullable, so this must be a leftJoin — an inner join
        // would make the edit page report "already deleted" for a teacher who
        // simply has no department assigned.
        $teacher = Teacher::select(
            'teachers.*',
            'users.*',
            'teachers.id AS id',
            'users.phone as user_phone',
            'users.role_id',
            'departments.department_name'
        )
            ->join('users', 'users.id', '=', 'teachers.user_id')
            ->leftJoin('departments', 'departments.id', '=', 'teachers.department_id')
            ->where('teachers.institute_id', get_institute_id())
            ->where('teachers.branch_id', get_branch_id())
            ->where('teachers.id', intval($id))
            ->first();

        if (! $teacher) {
            Alert::error('Error', _lang('Teacher already deleted.'));

            return redirect('teachers');
        }

        return view('academic::teachers.teacher-edit', [
            'teacher' => $teacher,
            'bloods' => get_blood_groups(),
            'departments' => Department::select('id', 'department_name')->get(),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $instituteId = get_institute_id();
        $branchId = get_branch_id();

        $teacher = $this->teacherService->findTeacherById($id);

        if (! $teacher) {
            Alert::error('Error', _lang('Teacher not found.'));

            return redirect()->route('teachers.index');
        }

        $request->validate([
            'name' => 'required|string|max:50',
            'phone' => [
                'required',
                'string',
                'regex:/^[0-9+\-\s()]{7,20}$/',
                Rule::unique('users', 'phone')->where(fn ($q) => $q->where('institute_id', $instituteId))->ignore($teacher->user_id),
            ],
            'email' => [
                'nullable',
                'email',
                'max:50',
                Rule::unique('users', 'email')->where(fn ($q) => $q->where('institute_id', $instituteId))->ignore($teacher->user_id),
            ],
            'password' => 'nullable|string|min:6|confirmed',
            'designation' => 'nullable|string|max:50',
            'department_id' => [
                'nullable',
                Rule::exists('departments', 'id')->where(fn ($q) => $q->where('institute_id', $instituteId)->where('branch_id', $branchId)),
            ],
            'birthday' => 'nullable|date|before:today',
            'gender' => 'nullable|string|max:50',
            'religion' => 'nullable|string|max:50',
            'sl' => 'nullable|integer',
            'blood' => 'nullable|string',
            'address' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $newImagePath = null;

        try {
            DB::beginTransaction();

            // ✅ UPDATE TEACHER
            $teacher->update([
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
                'is_administrator' => $request->is_administrator ?? 0,
                'access_key' => $request->password ?? $teacher->access_key, // only if given
            ]);

            $this->trackAction(UserLogAction::UPDATE, Teacher::class, $teacher->id, 'Teacher Updated');

            // ✅ UPDATE USER
            $user = $this->userService->findUserById((int) $teacher->user_id);

            if (! $user) {
                throw new Exception(_lang('Linked login account not found for this teacher.'));
            }

            // 🔥 IMAGE UPLOAD — reuse fileUploader's own old-image deletion instead of
            // reimplementing it against the wrong (public_path) location, which
            // silently failed to delete anything and orphaned every previous photo.
            $imagePath = $user->image;
            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $imagePath = fileUploader('users/', 'png', $request->file('image'), $user->image);
                $newImagePath = $imagePath;
            }

            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->image = $imagePath;

            // 🔥 PASSWORD UPDATE ONLY IF PROVIDED
            if (! empty($request->password)) {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            $this->trackAction(UserLogAction::UPDATE, User::class, $user->id, 'User Updated');

            DB::commit();

            Alert::success('Success', 'Teacher updated successfully');

            return redirect()->route('teachers.index');
        } catch (Exception $e) {
            DB::rollback();

            if ($newImagePath) {
                fileRemover('users/', $newImagePath);
            }

            Log::error('Teacher update failed: '.$e->getMessage(), ['teacher_id' => $id, 'exception' => $e]);

            Alert::error('Error', $e->getMessage());

            return back()->withInput();
        }
    }

    public function updateTeacherStatus(Request $request): Redirector|RedirectResponse
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

            return redirect('teachers');
        }

        $user->status = $request->status;
        $user->save();

        Alert::toast(_lang('Teacher has been updated.'), 'success');

        return redirect('teachers');
    }

    public function destroy(int $id): Redirector|RedirectResponse
    {
        try {
            DB::transaction(function () use ($id) {
                $teacher = $this->teacherService->findTeacherById($id);

                if (! $teacher) {
                    throw new Exception(_lang('Teacher not found.'));
                }

                $user = $this->userService->findUserById((int) $teacher->user_id);

                $this->trackAction(UserLogAction::DELETE, Teacher::class, $teacher->id, 'Teacher Delete');

                if ($user) {
                    $this->trackAction(UserLogAction::DELETE, User::class, $user->id, 'User Credentials Delete');
                    $this->userService->deleteUserById((int) $user->id);
                }

                $this->teacherService->deleteTeacherById((int) $teacher->id);
            });

            Alert::toast(_lang('Teacher has been deleted.'), 'success');
        } catch (Exception $e) {
            Log::error('Teacher delete failed: '.$e->getMessage(), ['teacher_id' => $id]);
            Alert::error('Error', $e->getMessage());
        }

        return redirect('teachers');
    }

    private function setTeacherStatus(int $id, string $status): Redirector|RedirectResponse
    {
        $teacher = $this->teacherService->findTeacherById($id);

        if (! $teacher) {
            Alert::error('Error', _lang('Teacher not found.'));

            return redirect('teachers');
        }

        try {
            DB::transaction(function () use ($teacher, $status) {
                $teacher->update(['status' => $status]);

                $user = $this->userService->findUserById((int) $teacher->user_id);
                if (! $user) {
                    throw new Exception(_lang('Linked login account not found for this teacher.'));
                }

                $user->update(['user_status' => $status]);
            });
        } catch (Exception $e) {
            Log::error('Teacher status toggle failed: '.$e->getMessage(), ['teacher_id' => $id]);
            Alert::error('Error', $e->getMessage());

            return redirect('teachers');
        }

        Alert::toast($status === '1' ? _lang('Teacher Enable Successfully.') : _lang('Teacher Disable Successfully.'), 'success');

        return redirect('teachers');
    }

    public function statusEnable(int $id): Redirector|RedirectResponse
    {
        return $this->setTeacherStatus((int) $id, '1');
    }

    public function statusDisable(int $id): Redirector|RedirectResponse
    {
        return $this->setTeacherStatus((int) $id, '0');
    }

    public function teacherMultipleDelete(Request $request): Redirector|RedirectResponse
    {
        $request->validate([
            'teacher_ids' => 'required|array|min:1',
            'teacher_ids.*' => 'integer|exists:teachers,id',
        ]);

        $deleted = 0;
        $failed = 0;

        foreach ($request->teacher_ids as $teacherId) {
            try {
                DB::transaction(function () use ($teacherId) {
                    $teacher = $this->teacherService->findTeacherById((int) $teacherId);

                    if (! $teacher) {
                        throw new Exception(_lang('Teacher not found.'));
                    }

                    $this->trackAction(UserLogAction::DELETE, Teacher::class, $teacher->id, 'Teacher Delete (Multiple)');

                    if ($teacher->user_id) {
                        $user = $this->userService->findUserById((int) $teacher->user_id);

                        if ($user) {
                            $this->trackAction(UserLogAction::DELETE, User::class, $user->id, 'User Credentials Delete (Multiple)');
                            $this->userService->deleteUserById((int) $user->id);
                        }
                    }

                    $this->teacherService->deleteTeacherById((int) $teacher->id);
                });

                $deleted++;
            } catch (Exception $e) {
                $failed++;
                Log::error('Bulk teacher delete failed: '.$e->getMessage(), ['teacher_id' => $teacherId]);
            }
        }

        if ($failed > 0) {
            Alert::error('Error', "{$deleted} "._lang('deleted').", {$failed} "._lang('failed').'. '._lang('Check logs for details.'));
        } else {
            Alert::toast(_lang('Selected teachers have been deleted.'), 'success');
        }

        return redirect('teachers');
    }
}
