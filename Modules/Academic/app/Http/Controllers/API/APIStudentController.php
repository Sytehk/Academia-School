<?php

namespace Modules\Academic\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Imports\StudentsImport;
use App\Services\AcademicContextService;
use App\Services\AdmissionNumberService;
use App\Traits\StudentCollectionTrait;
use App\Traits\Trackable;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Academic\Http\Requests\StudentCreateRequest;
use Modules\Academic\Http\Requests\StudentUpdateRequest;
use Modules\Academic\Models\CustomFieldValue;
use Modules\Academic\Models\LibraryMember;
use Modules\Academic\Models\Student;
use Modules\Academic\Models\StudentSession;
use Modules\Academic\Services\StudentService;
use Modules\Academic\Services\StudentSessionService;
use Modules\Academic\Services\UserService;
use Modules\Authentication\Models\Institute;
use Modules\Authentication\Models\User;
use Modules\ParentModule\Models\ParentModel;

class APIStudentController extends Controller
{
    use StudentCollectionTrait, Trackable;

    public function __construct(
        private readonly UserService $userService,
        private readonly StudentService $studentService,
        private readonly StudentSessionService $studentSessionService,
        private readonly AcademicContextService $academicContext,
        private readonly AdmissionNumberService $admissionNumbers,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $class_id = (int) $request->class_id;
        $section_id = (int) $request->section_id;
        $group_id = (int) $request->group_id;

        $perPage = (int) $request->per_page ?: 20; // 👈 default 20
        $search = $request->search;
        $sortBy = $request->sort_by ?? 'student_sessions.roll';
        $sortOrder = $request->sort_order ?? 'asc';

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

        return $this->responseSuccess($students, 'Students fetched successfully.');
    }

    public function store(StudentCreateRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $password = '123456';

            $imagePath = null;
            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $imagePath = fileUploader('users/', 'png', $request->file('image'));
            }

            $user = $this->userService->createOrUpdateUser([
                'name' => $request->first_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => $request->password ?? $password,
                'status' => 1,
                'image' => $imagePath,
                'user_type' => 'Student',
            ]);

            $student = $this->studentService->createStudent([
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
                    $request->registration_no ?? null
                ),
                'address' => $request->address,
                'access_key' => $request->password ?? $password,
                'information_sent_to_name' => $request->information_sent_to_name,
                'information_sent_to_relation' => $request->information_sent_to_relation,
                'information_sent_to_phone' => $request->information_sent_to_phone,
                'information_sent_to_address' => $request->information_sent_to_address,
            ], (int) $user->id);

            $sessionData = [
                'session_id' => (int) get_option('academic_year'),
                'student_id' => (int) $student->id,
                'class_id' => (int) $request->class_id,
                'section_id' => (int) $request->section_id,
                'roll' => (int) $request->roll,
                'group_id' => $request->group,
                'qr_code' => $request->qr_code ?? str_pad(random_int(0, 99999999), 8, '0', STR_PAD_LEFT),
            ];
            $this->studentSessionService->createStudentSession($sessionData);

            // Parent Create ->
            $parent = null;
            if (
                $request->filled('information_sent_to_name') &&
                $request->filled('information_sent_to_email') &&
                $request->filled('information_sent_to_phone')
            ) {

                $parentUser = $this->userService->createOrUpdateUser([
                    'name' => $request->information_sent_to_name,
                    'email' => $request->information_sent_to_email,
                    'phone' => $request->information_sent_to_phone,
                    'password' => $request->password ?? $password,
                    'status' => 1,
                    'user_type' => 'Parent',
                ]);

                // Parent Create OR Update
                $parent = ParentModel::updateOrCreate(
                    [
                        'user_id' => $parentUser->id, // unique match
                        'student_id' => $student->id, // unique match
                    ],
                    [
                        'institute_id' => get_institute_id(),
                        'branch_id' => get_branch_id(),
                        'parent_name' => $request->information_sent_to_name,
                        'parent_profession' => $request->parent_profession ?? 'Employee',
                        'parent_phone' => $request->information_sent_to_phone,
                        'present_address' => $request->information_sent_to_address,
                        'permanent_address' => $request->information_sent_to_address,
                        'access_key' => $request->password ?? $password,
                    ]
                );

                // Student Parent Assign.
                $student = Student::where('id', $student->id)->first();
                $student->update([
                    'parent_id' => $parent->id,
                ]);
            }

            // Library Membership
            LibraryMember::create([
                'institute_id' => get_institute_id(),
                'branch_id' => get_branch_id(),
                'user_id' => $user->id,
                'member_type' => 'Student',
                'library_id' => intval($request->roll),
                'student_id' => $student->id,
            ]);

            // --- HANDLE CUSTOM FIELDS ---
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
                        [
                            'value' => $value,
                            'status' => 1,
                        ]
                    );
                }
            }

            DB::commit();

            return $this->responseSuccess($student, 'Student has been created successfully.');
        } catch (Exception $e) {
            DB::rollBack();

            return $this->responseError([], $e->getMessage());
        }
    }

    public function show(int $id): JsonResponse
    {
        $student = $this->studentService->findStudentById((int) $id);
        if (! $student) {
            return $this->responseError([], _lang('Student not found.'), 404);
        }

        return $this->responseSuccess($student, 'Student have been fetched successfully.');
    }

    public function update(StudentUpdateRequest $request, int $id): JsonResponse
    {
        $student = $this->studentService->findStudentById((int) $id);
        if (! $student) {
            return $this->responseError([], _lang('Student not found.'), 404);
        }

        try {
            DB::beginTransaction();
            $password = '123456';

            $imagePath = $student->user?->image;
            if (! empty($request['image']) && $request['image'] instanceof UploadedFile) {
                $imagePath = fileUploader('users/', 'png', $request['image'], $student->user?->image);
            }

            $user = $student->user;
            $user->update([
                'name' => $request->first_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'image' => $imagePath,
            ]);

            $parentUser = User::where('phone', $request->information_sent_to_phone)->first();
            if ($parentUser) {
                $parentUser->update([
                    'name' => $request->information_sent_to_name,
                    'email' => $request->information_sent_to_email,
                ]);
            } else {
                $parentUser = User::create([
                    'name' => $request->information_sent_to_name,
                    'email' => $request->information_sent_to_email,
                    'phone' => $request->information_sent_to_phone,
                    'password' => bcrypt($request->password ?? $password),
                    'status' => 1,
                    'user_type' => 'Parent',
                ]);
            }

            // ✅ Update Student information.
            $this->studentService->updateStudent([
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
                'registration_no' => intval($request->registration_no ?? 0),
                'address' => $request->address,
                'access_key' => $request->password ?? $password,
                'information_sent_to_name' => $request->information_sent_to_name,
                'information_sent_to_relation' => $request->information_sent_to_relation,
                'information_sent_to_phone' => $request->information_sent_to_phone,
                'information_sent_to_address' => $request->information_sent_to_address,
            ], (int) $student->id);

            // ✅ Update or Create Library Membership
            LibraryMember::updateOrCreate(
                [
                    'institute_id' => get_institute_id(),
                    'branch_id' => get_branch_id(),
                    'user_id' => $user->id,
                    'student_id' => $student->id,
                    'member_type' => 'Student',
                ],
                ['library_id' => intval($request->roll), 'member_type' => 'Student']
            );

            // ✅ Update Student Session
            $studentSession = $this->studentSessionService->findStudentSessionByStudentId((int) $student->id);
            $sessionData = [
                'class_id' => intval($request->class_id),
                'section_id' => intval($request->section_id),
                'roll' => intval($request->roll),
                'group_id' => $request->group,
            ];

            // ✅ Only add branch_id if it exists (not null / not empty)
            if (! is_null($request->branch_id)) {
                $sessionData['branch_id'] = (int) $request->branch_id;
            }

            if ($studentSession) {
                $this->studentSessionService->updateStudentSession($sessionData, $studentSession->id);
            }

            // --- HANDLE CUSTOM FIELDS ---
            if ($request->filled('custom_fields') && is_array($request->custom_fields)) {
                foreach ($request->custom_fields as $fieldId => $value) {
                    // Update if exists, otherwise create new
                    $cf = CustomFieldValue::updateOrCreate(
                        [
                            'institute_id' => get_institute_id(),
                            'branch_id' => get_branch_id(),
                            'model_type' => 'student',
                            'model_id' => $student->id,
                            'custom_field_id' => $fieldId,
                        ],
                        [
                            'value' => $value,
                            'status' => 1,
                        ]
                    );

                    // Force updated_at refresh even if value didn’t change
                    $cf->touch(); // updates updated_at to now()
                }
            }

            DB::commit();

            $student = $this->studentService->findStudentById((int) $id);

            return $this->responseSuccess($student, 'Student has been updated successfully.');
        } catch (Exception $e) {
            DB::rollback();

            return $this->responseError([], $e->getMessage());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            return DB::transaction(function () use ($id) {
                $student = $this->studentService->findStudentById(intval($id));

                // Check if student exists
                if (! $student) {
                    throw new Exception('Student not found.');
                }

                // Delete student session only if it exists
                if (! empty($student->studentSession)) {
                    $this->studentSessionService->deleteStudentSessionById($student->studentSession->id);
                }

                // Delete user if exists
                if (! empty($student->user_id)) {
                    $this->userService->deleteUserById(intval($student->user_id));
                }

                // Delete student record
                $this->studentService->deleteStudentById(intval($student->id));

                return $this->responseSuccess([], 'Student has been deleted successfully.');
            });
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function updateStatus(Request $request): JsonResponse
    {
        $request->validate([
            'student_id' => 'required|integer',
            'type' => 'required|string|in:enable,disable', // Accepts only "enable" or "disable"
        ]);

        $student = $this->studentService->findStudentById((int) $request->student_id);
        if (! $student) {
            return $this->responseError([], 'Student Not Found', 404);
        }

        // Determine the status based on the type
        $status = $request->type === 'enable' ? '1' : '0';

        // Update student status
        $student->update(['status' => $status]);

        // Update associated user status
        $user = $this->userService->findUserById((int) $student->user_id);
        if ($user) {
            $user->update(['user_status' => $status]);
        }

        $message = $request->type === 'enable'
            ? 'Student has been enabled successfully.'
            : 'Student has been disabled successfully.';

        return $this->responseSuccess([], $message);
    }

    public function multipleDelete(Request $request): JsonResponse
    {
        $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'integer|exists:students,id',
        ]);

        foreach ($request->student_ids as $key => $student_id) {
            DB::transaction(function () use ($student_id) {
                $student = $this->studentService->findStudentById(intval($student_id));
                $this->studentSessionService->deleteStudentSessionById($student->studentSession->id);
                $this->userService->deleteUserById(intval($student->user_id));
                $this->studentService->deleteStudentById(intval($student->id));
            });
        }

        return $this->responseSuccess([], 'Students have been deleted successfully.');
    }

    public function downloadDemoFile()
    {
        $filePath = public_path('uploads/student_xlsx_file/student_demo.xlsx');

        if (file_exists($filePath)) {
            return response()->download($filePath, 'student_demo.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Demo file not found.',
        ], 404);
    }

    public function bulkImports(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file',
            'class_id' => 'required|integer|exists:classes,id',
            'academic_year' => 'required|integer|exists:academic_years,id',
            'section_id' => 'required|integer|exists:sections,id',
            'group' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->responseError([], $validator->errors()->first(), 422);
        }

        try {
            $this->academicContext->assertClassSectionGroup(
                (int) $request->class_id,
                (int) $request->section_id,
            );
        } catch (ValidationException $e) {
            return $this->responseError([], $e->getMessage(), 422);
        }

        $file = $request->file('file');
        $import = new StudentsImport;

        try {
            Excel::import($import, $file);
        } catch (Exception $e) {
            return $this->responseError([], 'Invalid Excel format or parsing error.', 400);
        }

        $importedData = $import->data ?? [];

        $success = [];
        $failed = [];

        foreach ($importedData as $index => $row) {

            DB::beginTransaction();

            try {

                $password = '12345678';

                // ==============================
                // 1️⃣ Duplicate Phone Check
                // ==============================
                if (! empty($row['mobile_no'])) {
                    $existingUser = User::where('phone', $row['mobile_no'])->first();
                    if ($existingUser) {
                        throw new Exception('Duplicate student phone: '.$row['mobile_no']);
                    }
                }

                // ==============================
                // 2️⃣ Create Student User
                // ==============================
                $user = User::create([
                    'institute_id' => get_institute_id(),
                    'branch_id' => get_branch_id(),
                    'name' => $row['name'],
                    'email' => $row['mobile_no'] ?? null,
                    'phone' => $row['mobile_no'] ?? null,
                    'password' => Hash::make($password),
                    'user_type' => 'Student',
                    'status' => 1,
                ]);

                // ==============================
                // 3️⃣ Create Student
                // ==============================
                $student = Student::create([
                    'institute_id' => get_institute_id(),
                    'branch_id' => get_branch_id(),
                    'user_id' => $user->id,
                    'first_name' => $row['name'],
                    'gender' => $row['gender'],
                    'religion' => $row['religion'],
                    'father_name' => $row['fathers_name'],
                    'mother_name' => $row['mothers_name'],
                    'phone' => $row['mobile_no'],
                    'registration_no' => $row['registration_no'],
                    'address' => $row['address'],
                    'access_key' => $password,
                    'information_sent_to_name' => $row['guardian_name'],
                    'information_sent_to_relation' => 'Guardian',
                    'information_sent_to_phone' => $row['guardian_number'],
                    'information_sent_to_address' => $row['address'],
                ]);

                // ==============================
                // 4️⃣ Student Session
                // ==============================
                StudentSession::create([
                    'institute_id' => get_institute_id(),
                    'branch_id' => get_branch_id(),
                    'session_id' => $request->academic_year,
                    'student_id' => $student->id,
                    'class_id' => $request->class_id,
                    'section_id' => $request->section_id,
                    'group_id' => $request->group,
                    'roll' => $row['roll_no'],
                    'qr_code' => $request->qr_code ?? str_pad(random_int(0, 99999999), 8, '0', STR_PAD_LEFT),
                ]);

                // ==============================
                // 5️⃣ Parent Create (If Exists)
                // ==============================
                if (! empty($row['guardian_name']) && ! empty($row['guardian_number'])) {
                    $parentUser = User::firstOrCreate(
                        ['phone' => $row['guardian_number']],
                        [
                            'institute_id' => get_institute_id(),
                            'branch_id' => get_branch_id(),
                            'name' => $row['guardian_name'],
                            'email' => $row['guardian_number'],
                            'password' => Hash::make($password),
                            'user_type' => 'Parent',
                            'status' => 1,
                        ]
                    );

                    $parentModel = ParentModel::updateOrCreate(
                        [
                            'user_id' => $parentUser->id,
                            'student_id' => $student->id,
                        ],
                        [
                            'institute_id' => get_institute_id(),
                            'branch_id' => get_branch_id(),
                            'parent_name' => $row['guardian_name'],
                            'parent_phone' => $row['guardian_number'],
                            'present_address' => $row['address'],
                            'permanent_address' => $row['address'],
                            'access_key' => $password,
                        ]
                    );

                    $student->update([
                        'parent_id' => $parentModel->id,
                    ]);
                }

                // ==============================
                // 6️⃣ Library Member
                // ==============================
                LibraryMember::create([
                    'institute_id' => get_institute_id(),
                    'branch_id' => get_branch_id(),
                    'user_id' => $user->id,
                    'member_type' => 'Student',
                    'library_id' => $row['roll_no'],
                    'student_id' => $student->id,
                ]);

                DB::commit();

                $success[] = [
                    'row' => $index + 1,
                    'name' => $row['name'],
                    'phone' => $row['mobile_no'],
                    'roll_no' => $row['roll_no'],
                    'default_password' => $password,
                ];
            } catch (Exception $e) {

                DB::rollBack();

                $failed[] = [
                    'row' => $index + 1,
                    'error' => $e->getMessage(),
                    'data' => $row,
                ];
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Bulk upload completed',
            'successful_records' => $success,
            'failed_records' => $failed,
        ]);
    }

    public function studentBranchMigration(Request $request): JsonResponse
    {
        $request->validate([
            'student_id' => 'required|integer|exists:students,id',
            'branch_id' => 'required|integer|exists:branches,id',
        ]);

        try {
            $studentSession = $this->academicContext->studentSessionForStudent((int) $request->student_id);
            if (! $studentSession) {
                return $this->responseError([], 'Student session not found');
            }
        } catch (\InvalidArgumentException $e) {
            return $this->responseError([], $e->getMessage());
        }

        if ($studentSession->branch_id == (int) $request->branch_id) {
            return $this->responseError([], 'Student already in this branch');
        }

        // ✅ Update branch_id
        $studentSession->update([
            'branch_id' => (int) $request->branch_id,
        ]);

        return $this->responseSuccess([], 'Student migration successfully done');
    }
}
