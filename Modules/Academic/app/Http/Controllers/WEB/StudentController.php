<?php

namespace Modules\Academic\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Services\AdmissionNumberService;
use App\Traits\StudentCollectionTrait;
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
use Modules\Academic\Http\Requests\StudentCreateRequest;
use Modules\Academic\Http\Requests\StudentUpdateRequest;
use Modules\Academic\Models\CustomFieldValue;
use Modules\Academic\Models\LibraryMember;
use Modules\Academic\Models\Student;
use Modules\Academic\Services\ClassService;
use Modules\Academic\Services\SectionService;
use Modules\Academic\Services\StudentService;
use Modules\Academic\Services\StudentSessionService;
use Modules\Academic\Services\UserService;
use Modules\Authentication\Models\Institute;
use Modules\Authentication\Models\User;
use Modules\ParentModule\Models\ParentModel;
use RealRashid\SweetAlert\Facades\Alert;

class StudentController extends Controller
{
    use StudentCollectionTrait, Trackable;

    public function __construct(
        private readonly UserService $userService,
        private readonly ClassService $classService,
        private readonly SectionService $sectionService,
        private readonly StudentService $studentService,
        private readonly StudentSessionService $studentSessionService,
        private readonly AdmissionNumberService $admissionNumbers
    ) {}

    public function index(Request $request): View|Factory
    {
        $class_id = (int) $request->class_id;
        $section_id = (int) $request->section_id;
        $group_id = (int) $request->group_id;

        $perPage = (int) ($request->per_page ?? 20);
        $search = $request->search;
        $sortBy = $request->sort_by ?? 'student_sessions.roll';
        $sortOrder = $request->sort_order ?? 'asc';
        $currentPage = $request->get('page', 1);

        $students = $this->studentService->getStudentsByClassSectionGroupList(
            $class_id,
            $section_id,
            $group_id,
            $perPage,
            get_institute_id(),
            get_branch_id(),
            $search,
            $sortBy,
            $sortOrder
        );

        $sections = $this->sectionService->getSections();

        return view('academic::students.student-list', [
            'students' => $students,
            'sections' => $sections,
            'currentPage' => $currentPage, // Pass current page if needed in view
        ]);
    }

    public function create($id = null): Factory|View
    {
        return view('academic::students.student-add', [
            'sections' => $this->sectionService->getSections(),
        ]);
    }

    public function store(StudentCreateRequest $request): RedirectResponse
    {
        $imagePath = null;

        try {
            DB::beginTransaction();

            $password = $request->password ?? '123456';

            // ✅ Image Upload (kept outside rollback scope on the DB side; removed below if the transaction fails)
            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $imagePath = fileUploader('users/', 'png', $request->file('image'));
            }

            // ✅ Create User
            $user = $this->userService->createOrUpdateUser([
                'institute_id' => get_institute_id(),
                'branch_id' => get_branch_id(),
                'name' => trim($request->first_name.' '.$request->last_name),
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => $password,
                'status' => 1,
                'image' => $imagePath,
                'user_type' => 'Student',
            ]);

            if (! $user) {
                throw new Exception(_lang('Student login account could not be created.'));
            }

            // ✅ Create Student
            $student = $this->studentService->createStudent([
                'institute_id' => get_institute_id(),
                'branch_id' => get_branch_id(),
                'user_id' => $user->id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'father_name' => $request->father_name,
                'mother_name' => $request->mother_name,
                'birthday' => $request->birthday,
                'gender' => $request->gender,
                'blood_group' => $request->blood_group,
                'religion' => $request->religion,
                'phone' => $request->phone,
                'registration_no' => $this->admissionNumbers->resolve(
                    Institute::findOrFail(get_institute_id()),
                    $request->registration_no
                ),
                'address' => $request->address,
                'access_key' => $password,
                'information_sent_to_name' => $request->information_sent_to_name,
                'information_sent_to_relation' => $request->information_sent_to_relation,
                'information_sent_to_phone' => $request->information_sent_to_phone,
                'information_sent_to_address' => $request->information_sent_to_address,
            ], $user->id);

            if (! $student) {
                throw new Exception(_lang('Student record could not be created.'));
            }

            // ✅ Session (qr_code is guaranteed unique within the institute)
            $studentSession = $this->studentSessionService->createStudentSession([
                'session_id' => get_option('academic_year'),
                'student_id' => $student->id,
                'class_id' => $request->class_id,
                'section_id' => $request->section_id,
                'roll' => $request->roll,
                'group_id' => $request->group,
                'qr_code' => $this->studentSessionService->resolveQrCode($request->qr_code),
            ]);

            if (! $studentSession) {
                throw new Exception(_lang('Student session record could not be created.'));
            }

            // ✅ Parent Create (same as API)
            if (
                $request->filled('information_sent_to_name') &&
                $request->filled('information_sent_to_phone')
            ) {

                $parentUser = $this->userService->createOrUpdateUser([
                    'institute_id' => get_institute_id(),
                    'branch_id' => get_branch_id(),
                    'name' => $request->information_sent_to_name,
                    'email' => $request->information_sent_to_email,
                    'phone' => $request->information_sent_to_phone,
                    'password' => $password,
                    'status' => 1,
                    'user_type' => 'Parent',
                ]);

                if (! $parentUser) {
                    throw new Exception(_lang('Guardian login account could not be created.'));
                }

                $parent = ParentModel::updateOrCreate(
                    [
                        'user_id' => $parentUser->id,
                        'student_id' => $student->id,
                    ],
                    [
                        'institute_id' => get_institute_id(),
                        'branch_id' => get_branch_id(),
                        'parent_name' => $request->information_sent_to_name,
                        'parent_phone' => $request->information_sent_to_phone,
                        'present_address' => $request->information_sent_to_address,
                        'access_key' => $password,
                    ]
                );

                $student->update(['parent_id' => $parent->id]);
            }

            // ✅ Library
            LibraryMember::create([
                'institute_id' => get_institute_id(),
                'branch_id' => get_branch_id(),
                'user_id' => $user->id,
                'member_type' => 'Student',
                'library_id' => (int) $request->roll,
                'student_id' => $student->id,
            ]);

            // ✅ Custom Fields
            if ($request->filled('custom_fields') && is_array($request->custom_fields)) {
                foreach ($request->custom_fields as $fieldId => $value) {
                    CustomFieldValue::updateOrCreate(
                        [
                            'institute_id' => get_institute_id(),
                            'branch_id' => get_branch_id(),
                            'model_type' => 'student',
                            'model_id' => $student->id,
                            'custom_field_id' => $fieldId,
                        ],
                        ['value' => $value, 'status' => 1]
                    );
                }
            }

            DB::commit();

            Alert::toast('Student Created Successfully!', 'success');

            return redirect()->route('students.index');
        } catch (Exception $e) {
            DB::rollback();

            // The image file was already written to disk before the failure; remove it so it doesn't orphan.
            if ($imagePath) {
                fileRemover('users/', $imagePath);
            }

            Log::error('Student store failed: '.$e->getMessage(), ['exception' => $e]);

            Alert::error('Error', $e->getMessage());

            return back()->withInput();
        }
    }

    public function show(int $id): Factory|View|RedirectResponse
    {
        $student = $this->studentService->findStudentById((int) $id);

        if (! $student) {
            Alert::error('Error', 'Student not found.');

            return redirect('students');
        }

        return view('academic::students.student-view', compact('student'));
    }

    public function edit(int $id): Factory|View|RedirectResponse
    {
        $student = $this->studentService->findStudentById($id);

        if (! $student) {
            Alert::error('Error', 'Student not found.');

            return redirect('students');
        }

        return view('academic::students.student-edit', [
            'classes' => $this->classService->getClasses(),
            'sections' => $this->sectionService->getSections(),
            'student' => $student,
        ]);
    }

    public function update(StudentUpdateRequest $request, int $id): Redirector|RedirectResponse
    {
        $student = $this->studentService->findStudentById((int) $id);
        if (! $student) {
            Alert::error('Error', 'Student not found.');

            return redirect('students');
        }

        $newImagePath = null;
        $oldImagePath = $student->user?->image;

        try {
            DB::beginTransaction();
            $password = '123456';

            // ✅ Handle image upload
            $imagePath = $oldImagePath;
            if (! empty($request->image) && $request->image instanceof UploadedFile) {
                $imagePath = fileUploader('users/', 'png', $request->image, $oldImagePath);
                $newImagePath = $imagePath;
            }

            if (! $student->user) {
                throw new Exception(_lang('Linked login account not found for this student.'));
            }

            // ✅ Update student user details
            $student->user->update([
                'name' => trim($request->first_name.' '.$request->last_name),
                'email' => $request->email,
                'phone' => $request->phone,
                'image' => $imagePath,
            ]);

            // ✅ Update password ONLY if provided
            if (! empty($request->password)) {
                $student->user->update([
                    'password' => Hash::make($request->password),
                ]);
            }

            // ✅ Update student info
            $this->studentService->updateStudent([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'father_name' => $request->father_name,
                'mother_name' => $request->mother_name,
                'birthday' => $request->birthday,
                'gender' => $request->gender,
                'blood_group' => $request->blood_group,
                'religion' => $request->religion,
                'phone' => $request->phone,
                'registration_no' => $request->registration_no,
                'address' => $request->address,
                'access_key' => $request->password ?? $student->access_key,
                'information_sent_to_name' => $request->information_sent_to_name,
                'information_sent_to_relation' => $request->information_sent_to_relation,
                'information_sent_to_phone' => $request->information_sent_to_phone,
                'information_sent_to_address' => $request->information_sent_to_address,
            ], (int) $student->id);

            // ✅ Parent / Guardian sync (mirrors store(), keeps parent_models + student.parent_id in sync)
            if (
                $request->filled('information_sent_to_name') &&
                $request->filled('information_sent_to_phone')
            ) {
                // User is deliberately not tenant-scoped by a global scope (it is
                // shared auth infrastructure, see BelongsToTenant's exclusions), so
                // this lookup must scope itself explicitly. Without institute_id,
                // a guardian phone number already registered under a DIFFERENT
                // institute matched here, and the lines below then updated that
                // foreign user's name/email -- a cross-tenant write reachable
                // through an ordinary student edit.
                $parentUser = User::where('phone', $request->information_sent_to_phone)
                    ->where('user_type', 'Parent')
                    ->where('institute_id', get_institute_id())
                    ->first();

                if ($parentUser) {
                    $parentUser->update([
                        'name' => $request->information_sent_to_name,
                        'email' => $request->information_sent_to_email,
                    ]);
                } else {
                    $parentUser = $this->userService->createOrUpdateUser([
                        'institute_id' => get_institute_id(),
                        'branch_id' => get_branch_id(),
                        'name' => $request->information_sent_to_name,
                        'email' => $request->information_sent_to_email,
                        'phone' => $request->information_sent_to_phone,
                        'password' => $request->password ?? $password,
                        'status' => 1,
                        'user_type' => 'Parent',
                    ]);
                }

                if (! $parentUser) {
                    throw new Exception(_lang('Guardian login account could not be created.'));
                }

                $parent = ParentModel::updateOrCreate(
                    [
                        'user_id' => $parentUser->id,
                        'student_id' => $student->id,
                    ],
                    [
                        'institute_id' => get_institute_id(),
                        'branch_id' => get_branch_id(),
                        'parent_name' => $request->information_sent_to_name,
                        'parent_phone' => $request->information_sent_to_phone,
                        'present_address' => $request->information_sent_to_address,
                        'access_key' => $request->password ?? $password,
                    ]
                );

                $student->update(['parent_id' => $parent->id]);
            }

            // ✅ Update or create Library Member
            LibraryMember::updateOrCreate(
                [
                    'institute_id' => get_institute_id(),
                    'branch_id' => get_branch_id(),
                    'user_id' => $student->user?->id,
                    'student_id' => $student->id,
                ],
                ['library_id' => intval($request->roll), 'member_type' => 'Student']
            );

            // ✅ Update Student Session (qr_code preserved unless changed to a genuinely unique value)
            $studentSession = $this->studentSessionService->findStudentSessionByStudentId((int) $student->id);

            if (! $studentSession) {
                throw new Exception(_lang('Student session record not found; cannot update class/section/roll.'));
            }

            $sessionData = [
                'class_id' => intval($request->class_id),
                'section_id' => intval($request->section_id),
                'roll' => intval($request->roll),
                'group_id' => $request->group,
                'qr_code' => $this->studentSessionService->resolveQrCode($request->qr_code, $studentSession->id),
            ];
            if (! is_null($request->branch_id)) {
                $sessionData['branch_id'] = (int) $request->branch_id;
            }

            $this->studentSessionService->updateStudentSession($sessionData, $studentSession->id);

            // ✅ Handle custom fields
            if ($request->filled('custom_fields') && is_array($request->custom_fields)) {
                foreach ($request->custom_fields as $fieldId => $value) {
                    $cf = CustomFieldValue::updateOrCreate(
                        [
                            'institute_id' => get_institute_id(),
                            'branch_id' => get_branch_id(),
                            'model_type' => 'student',
                            'model_id' => $student->id,
                            'custom_field_id' => $fieldId,
                        ],
                        ['value' => $value, 'status' => 1]
                    );
                    $cf->touch(); // refresh updated_at
                }
            }

            DB::commit();

            Alert::toast('Student Update successful!', 'success');

            return redirect('students');
        } catch (Exception $e) {
            DB::rollback();

            // A new image was written to disk before the failure; remove it and keep the old one referenced in DB.
            if ($newImagePath) {
                fileRemover('users/', $newImagePath);
            }

            Log::error('Student update failed: '.$e->getMessage(), ['student_id' => $id, 'exception' => $e]);

            Alert::error('Error', $e->getMessage());

            return redirect('students');
        }
    }

    /**
     * Cascade-delete every record owned by a student while respecting FK constraints.
     * Soft-deletable rows (student, user, student_session, parent_models, custom_field_values)
     * are soft-deleted; the plain library_members row is hard-deleted since it has no soft-delete column.
     */
    private function deleteStudentRelatedRecords(Student $student): void
    {
        $studentSession = $this->studentSessionService->findStudentSessionByStudentId((int) $student->id);
        if ($studentSession) {
            $this->studentSessionService->deleteStudentSessionById($studentSession->id);
        }

        ParentModel::where('student_id', $student->id)->delete();

        CustomFieldValue::where('model_type', 'student')
            ->where('model_id', $student->id)
            ->delete();

        LibraryMember::where('student_id', $student->id)->delete();

        if ($student->user_id) {
            $this->userService->deleteUserById((int) $student->user_id);
        }

        $this->studentService->deleteStudentById((int) $student->id);
    }

    public function destroy(int $id): Redirector|RedirectResponse
    {
        try {
            DB::transaction(function () use ($id) {
                $student = $this->studentService->findStudentById(intval($id));

                if (! $student) {
                    throw new Exception(_lang('Student not found.'));
                }

                $this->deleteStudentRelatedRecords($student);
            });

            Alert::toast(_lang('Student data has been deleted.'), 'success');

            return redirect('students');
        } catch (Exception $e) {
            Alert::error('Error', $e->getMessage());

            return redirect('students');
        }
    }

    private function setStudentStatus(int $id, string $status): Redirector|RedirectResponse
    {
        $student = $this->studentService->findStudentById($id);
        if (! $student) {
            Alert::error('Error', 'Student Not Found');

            return redirect('students');
        }

        try {
            DB::transaction(function () use ($student, $status) {
                $student->update(['status' => $status]);

                $user = $this->userService->findUserById((int) $student->user_id);
                if (! $user) {
                    throw new Exception(_lang('Linked login account not found for this student.'));
                }

                $user->update(['user_status' => $status]);
            });
        } catch (Exception $e) {
            Log::error('Student status toggle failed: '.$e->getMessage(), ['student_id' => $id]);
            Alert::error('Error', $e->getMessage());

            return redirect('students');
        }

        Alert::toast($status === '1' ? _lang('Student Enable Successfully.') : _lang('Student Disable Successfully.'), 'success');

        return redirect('students');
    }

    public function statusEnable(int $id): Redirector|RedirectResponse
    {
        return $this->setStudentStatus((int) $id, '1');
    }

    public function statusDisable(int $id): Redirector|RedirectResponse
    {
        return $this->setStudentStatus((int) $id, '0');
    }

    public function multipleDelete(Request $request): Redirector|RedirectResponse
    {
        $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'integer|exists:students,id',
        ]);

        $deleted = 0;
        $failed = 0;

        foreach ($request->student_ids as $studentId) {
            try {
                DB::transaction(function () use ($studentId) {
                    $student = $this->studentService->findStudentById((int) $studentId);

                    if (! $student) {
                        throw new Exception(_lang('Student not found.'));
                    }

                    $this->deleteStudentRelatedRecords($student);
                });

                $deleted++;
            } catch (Exception $e) {
                $failed++;
                Log::error('Bulk student delete failed: '.$e->getMessage(), ['student_id' => $studentId]);
            }
        }

        if ($failed > 0) {
            Alert::error('Error', "{$deleted} ".(_lang('deleted')).", {$failed} ".(_lang('failed')).'. '._lang('Check logs for details.'));
        } else {
            Alert::toast(_lang('Students data has been deleted.'), 'success');
        }

        return redirect('students');
    }

    public function atAGlance(): Factory|View
    {
        $students = Student::query()
            ->select('students.*', 'student_sessions.roll', 'student_sessions.qr_code', 'classes.class_name', 'sections.section_name', 'student_groups.group_name')
            ->join('users', 'users.id', '=', 'students.user_id')
            ->join('student_sessions', 'students.id', '=', 'student_sessions.student_id')
            ->leftJoin('classes', 'classes.id', '=', 'student_sessions.class_id')
            ->leftJoin('sections', 'sections.id', '=', 'student_sessions.section_id')
            ->leftJoin('student_groups', 'student_sessions.group_id', '=', 'student_groups.id')
            ->where('student_sessions.session_id', get_option('academic_year'))
            ->where('student_sessions.institute_id', get_institute_id())
            ->where('student_sessions.branch_id', get_branch_id())
            ->orderBy('student_sessions.roll', 'ASC')
            ->get();

        return view('academic::students.at_a_glance.index', [
            'students' => $students,
        ]);
    }
}
