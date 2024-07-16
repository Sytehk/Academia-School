<?php

namespace Modules\Frontend\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Services\AdmissionNumberService;
use App\Traits\RequestSanitizerTrait;
use Exception;
use Illuminate\Support\Facades\DB;
use Modules\Academic\Models\LibraryMember;
use Modules\Academic\Models\Section;
use Modules\Academic\Models\Student;
use Modules\Academic\Models\StudentSession;
use Modules\Academic\Services\StudentService;
use Modules\Academic\Services\StudentSessionService;
use Modules\Academic\Services\UserService;
use Modules\Authentication\Models\Institute;
use Modules\Authentication\Models\User;
use Modules\Frontend\Http\Requests\AdmissionForm\AdmissionFormStoreRequest;
use Modules\Frontend\Http\Requests\AdmissionForm\AdmissionFormUpdateRequest;
use Modules\Frontend\Models\AdmissionForm;
use Modules\Frontend\Repositories\AdmissionFormRepository;
use Modules\ParentModule\Models\ParentModel;

class AdmissionFormController extends Controller
{
    use RequestSanitizerTrait;

    public function __construct(
        private readonly UserService $userService,
        private readonly StudentService $studentService,
        private readonly StudentSessionService $studentSessionService,
        private readonly AdmissionFormRepository $repo,
        private readonly AdmissionNumberService $admissionNumbers,
    ) {}

    public function index()
    {
        try {
            $admissionForms = AdmissionForm::where('institute_id', get_institute_id())
                ->where('status', 'Pending')
                ->latest()
                ->paginate(15);

            return view('frontend::admission_form.index', compact('admissionForms'));
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Create Page
     */
    public function create()
    {
        return view('frontend::admission_form.create');
    }

    public function store(AdmissionFormStoreRequest $request)
    {

        try {
            $this->repo->create($request->all());

            return redirect()
                ->route('admission-forms.index')
                ->with('success', 'AdmissionForm has been created successfully');
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    public function edit(int $id)
    {
        try {
            $repo = $this->repo->getById($id);

            return view('frontend::admission_form.edit', compact('repo', 'id'));
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    public function update(AdmissionFormUpdateRequest $request, int $id)
    {
        try {
            $this->repo->update(
                $id,
                $this->getUpdateRequest($request)
            );

            return redirect()
                ->route('admission-forms.index')
                ->with('success', 'Academic Image updated successfully');
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy(int $id)
    {
        try {

            $repo = $this->repo->getById($id);

            if (! $repo) {

                return back()->with('error', 'Academic Image Not Found');
            }

            // Remove image if exists
            if (! empty($repo->image)) {

                fileRemover('academic-images/', $repo->image);
            }

            $this->repo->delete($id);

            return redirect()
                ->route('admission-forms.index')
                ->with('success', 'Academic Image deleted successfully');
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }

    public function approve(int $id)
    {
        DB::beginTransaction();

        try {

            $admission = AdmissionForm::where('institute_id', get_institute_id())
                ->findOrFail($id);
            $classSection = Section::where('class_id', $admission->class_id)->first();

            if ($admission->status !== 'Pending') {
                DB::rollBack();

                return back()->with('error', 'Already processed.');
            }

            // Duplicate-prevention: the manual "Add Student" form enforces unique phone/email
            // per institute via StudentCreateRequest — approve() bypassed that entirely, so
            // approving two admission forms sharing a phone/email silently created two
            // separate User/Student records.
            $duplicateUser = User::where('institute_id', $admission->institute_id)
                ->where('phone', $admission->phone)
                ->first();
            $duplicateStudent = Student::where('institute_id', $admission->institute_id)
                ->where('phone', $admission->phone)
                ->exists();

            if ($duplicateUser || $duplicateStudent) {
                DB::rollBack();

                return back()->with('error', 'A student with this phone number already exists in this institute. Resolve the duplicate before approving.');
            }

            $password = '123456';

            // =========================
            // 1. CREATE USER (STUDENT)
            // =========================

            $imagePath = $admission->image
                ? $admission->image
                : null;

            $user = $this->userService->createOrUpdateUser([
                'institute_id' => $admission->institute_id,
                'branch_id' => $admission->branch_id,
                'name' => $admission->first_name,
                'email' => $admission->email,
                'phone' => $admission->phone,
                'password' => $password,
                'status' => 1,
                'image' => $imagePath,
                'user_type' => 'Student',
            ]);

            // =========================
            // 2. CREATE STUDENT
            // =========================
            $student = $this->studentService->createStudent([
                'institute_id' => $admission->institute_id,
                'branch_id' => $admission->branch_id,
                'user_id' => $user->id,
                'first_name' => $admission->first_name,
                'last_name' => $admission->last_name,
                'father_name' => $admission->father_name,
                'mother_name' => $admission->mother_name,
                'birthday' => $admission->birthday,
                'gender' => $admission->gender,
                'blood_group' => $admission->blood_group,
                'religion' => $admission->religion,
                'phone' => $admission->phone,
                'address' => $admission->address,
                'registration_no' => $this->admissionNumbers->resolve(
                    Institute::findOrFail($admission->institute_id),
                    $admission->registration_no
                ),
                'access_key' => $password,
            ], $user->id);

            // =========================
            // 3. STUDENT SESSION
            // =========================
            $academicYear = get_option('academic_year');
            $lastRoll = StudentSession::where('institute_id', $admission->institute_id)
                ->where('branch_id', $admission->branch_id)
                ->where('session_id', $academicYear)
                ->where('class_id', $admission->class_id)
                ->where('section_id', $classSection->id)
                ->max('roll');
            $nextRoll = $lastRoll ? $lastRoll + 1 : 1;
            $this->studentSessionService->createStudentSession([
                'session_id' => $academicYear,
                'student_id' => $student->id,
                'class_id' => $admission->class_id,
                'section_id' => $classSection->id,
                'roll' => $nextRoll,
                'qr_code' => $this->studentSessionService->resolveQrCode(null),
            ]);

            // =========================
            // 4. PARENT CREATE
            // =========================
            if ($admission->guardian_name && $admission->guardian_phone) {
                $parentUser = $this->userService->createOrUpdateUser([
                    'name' => $admission->guardian_name,
                    'email' => $admission->guardian_email,
                    'phone' => $admission->guardian_phone,
                    'password' => $password,
                    'status' => 1,
                    'user_type' => 'Parent',
                ]);

                $parent = ParentModel::updateOrCreate(
                    [
                        'user_id' => $parentUser->id,
                        'student_id' => $student->id,
                    ],
                    [
                        'institute_id' => $admission->institute_id,
                        'branch_id' => $admission->branch_id,
                        'parent_name' => $admission->guardian_name,
                        'parent_phone' => $admission->guardian_phone,
                        'present_address' => $admission->guardian_address,
                        'access_key' => $password,
                    ]
                );

                $student->update(['parent_id' => $parent->id]);
            }

            // =========================
            // 5. LIBRARY MEMBER
            // =========================
            LibraryMember::create([
                'institute_id' => $admission->institute_id,
                'branch_id' => $admission->branch_id,
                'user_id' => $user->id,
                'member_type' => 'Student',
                'library_id' => $student->id,
                'student_id' => $student->id,
            ]);

            // =========================
            // 6. UPDATE STATUS
            // =========================
            $admission->update([
                'status' => 'Approved',
                'approved_at' => now(),
                'approved_by' => getUserId(),
            ]);

            DB::commit();

            return back()->with('success', 'Admission Approved & Student Created Successfully!');
        } catch (Exception $e) {

            DB::rollBack();

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Reject a pending admission form. No User/Student record is created —
     * mirrors approve()'s guard so a form can only be processed once.
     */
    public function reject(int $id)
    {
        try {
            $admission = AdmissionForm::where('institute_id', get_institute_id())
                ->findOrFail($id);

            if ($admission->status !== 'Pending') {
                return back()->with('error', 'Already processed.');
            }

            $admission->update([
                'status' => 'Rejected',
                'approved_at' => now(),
                'approved_by' => getUserId(),
            ]);

            return back()->with('success', 'Admission form rejected.');
        } catch (Exception $e) {

            return back()->with('error', $e->getMessage());
        }
    }
}
