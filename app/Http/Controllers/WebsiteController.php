<?php

namespace App\Http\Controllers;

use App\Helpers\InstituteHelper;
use App\Services\AcademicContextService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Modules\Academic\Models\Department;
use Modules\Academic\Models\Event;
use Modules\Academic\Models\Notice;
use Modules\Academic\Models\Signature;
use Modules\Academic\Models\Staff;
use Modules\Academic\Models\Student;
use Modules\Academic\Models\Subject;
use Modules\Academic\Models\Teacher;
use Modules\Academic\Services\StudentService;
use Modules\Elearning\Models\Course;
use Modules\Examination\Models\ClassExam;
use Modules\Frontend\Models\AboutUs;
use Modules\Frontend\Models\AcademicImage;
use Modules\Frontend\Models\Banner;
use Modules\Frontend\Models\FaqQuestion;
use Modules\Frontend\Models\MobileAppSection;
use Modules\Frontend\Models\OurHistory;
use Modules\Frontend\Models\Page;
use Modules\Frontend\Models\ReadyToJoinUs;
use Modules\Frontend\Models\Testimonial;
use Modules\Frontend\Models\WhyChooseUs;
use Modules\SystemConfiguration\Models\Setting;
use RealRashid\SweetAlert\Facades\Alert;

class WebsiteController extends Controller
{
    public function __construct(
        private readonly StudentService $studentService,
    ) {}

    public function index(Request $request)
    {
        /*
    |--------------------------------------------------------------------------
    | Detect Domain
    |--------------------------------------------------------------------------
    */

        $host = $request->getHost();
        /*
    |--------------------------------------------------------------------------
    | Find Institute
    |--------------------------------------------------------------------------
    */
        $institute = InstituteHelper::resolveInstituteFromRequest($request);
        if (! $institute) {
            abort(404, 'Institute not found');
        }

        $instituteId = $institute->id;

        /*
    |--------------------------------------------------------------------------
    | Cache Homepage Data
    |--------------------------------------------------------------------------
    */

        $data =
            [
                'banners' => Banner::where('institute_id', $instituteId)
                    ->latest()
                    ->get(),

                'about' => AboutUs::where('institute_id', $instituteId)
                    ->where('status', 1)
                    ->first(),

                'academicImages' => AcademicImage::where(
                    'institute_id',
                    $instituteId
                )->get(),

                'whyChooseUs' => WhyChooseUs::where(
                    'institute_id',
                    $instituteId
                )->get(),

                'readyToJoinUs' => ReadyToJoinUs::where(
                    'institute_id',
                    $instituteId
                )->get(),

                'teachers' => Teacher::with('user')
                    ->where('institute_id', $instituteId)
                    ->limit(4)
                    ->get(),

                'staffs' => Staff::with('user')
                    ->where('institute_id', $instituteId)
                    ->limit(4)
                    ->get(),

                'events' => Event::where('institute_id', $instituteId)
                    ->latest()
                    ->limit(5)
                    ->get(),

                'notices' => Notice::where('institute_id', $instituteId)
                    ->latest()
                    ->limit(5)
                    ->get(),

                'faqs' => FaqQuestion::where(
                    'institute_id',
                    $instituteId
                )->get(),

                'history' => OurHistory::where(
                    'institute_id',
                    $instituteId
                )->first(),

                'testimonials' => Testimonial::where(
                    'institute_id',
                    $instituteId
                )->get(),

                'mobileAppSections' => MobileAppSection::where(
                    'institute_id',
                    $instituteId
                )->get(),

                'courses' => Course::where(
                    'institute_id',
                    $instituteId
                )
                    ->limit(6)
                    ->get(),

                'student_count' => Student::where('institute_id', $instituteId)->count(),
            ];

        return view(
            'themes.theme-one.pages.index',
            compact(
                'institute',
                'data'
            )
        );
    }

    public function show(Request $request, $slug)
    {
        $institute = InstituteHelper::resolveInstituteFromRequest($request);

        $page = Page::where('slug', $slug)
            ->when($institute, function ($query) use ($institute): void {
                $query->where('institute_id', $institute->id);
            })
            ->firstOrFail();

        return view('themes.theme-one.pages.page', compact('page'));
    }

    /**
     * Build a signed token for the public /student-vital/{encodedData} route. The signature
     * prevents a visitor from forging or altering institute_id/branch_id/student_id to view
     * another tenant's student — see vital() below.
     */
    public static function buildStudentVitalToken(int $instituteId, int $branchId, int $studentId): string
    {
        $payload = [
            'institute_id' => $instituteId,
            'branch_id' => $branchId,
            'student_id' => $studentId,
        ];
        $payload['sig'] = hash_hmac('sha256', "{$instituteId}|{$branchId}|{$studentId}", config('app.key'));

        return base64_encode(json_encode($payload));
    }

    public function vital(string $encodedData)
    {
        try {
            // Decode
            $decoded = base64_decode($encodedData, true);

            if ($decoded === false) {
                return abort(404, 'Invalid request.');
            }

            $data = json_decode($decoded, true);

            // Validate structure
            if (
                ! is_array($data) ||
                ! isset($data['institute_id'], $data['branch_id'], $data['student_id'], $data['sig']) ||
                ! ctype_digit((string) $data['institute_id']) ||
                ! ctype_digit((string) $data['branch_id']) ||
                ! ctype_digit((string) $data['student_id'])
            ) {
                return abort(404, 'Invalid data format.');
            }

            $instituteId = (int) $data['institute_id'];
            $branchId = (int) $data['branch_id'];
            $studentId = (int) $data['student_id'];

            // Reject any payload whose signature doesn't match — prevents visitors from
            // forging institute_id/branch_id/student_id to view another tenant's student.
            $expectedSig = hash_hmac('sha256', "{$instituteId}|{$branchId}|{$studentId}", config('app.key'));
            if (! hash_equals($expectedSig, (string) $data['sig'])) {
                return abort(404, 'Invalid or expired link.');
            }

            // Look up the student scoped to the signed institute/branch — never via an
            // unscoped repository lookup, since this route has no authenticated tenant context.
            $student = Student::where('id', $studentId)
                ->where('institute_id', $instituteId)
                ->where('branch_id', $branchId)
                ->first();

            if (! $student) {
                return abort(404, 'Student not found.');
            }

            $exams = ClassExam::with('exam')
                ->where('class_id', $student->studentSession?->class_id)
                ->get()
                ->pluck('exam');

            $subjects = Subject::where('class_id', $student->studentSession?->class_id)->get();

            return view('vital.index', compact('student', 'exams', 'subjects', 'studentId', 'instituteId', 'branchId'));
        } catch (\Throwable $e) {
            return abort(404, 'Something went wrong.');
        }
    }

    public function studentIdCardPage()
    {
        return view('backend.students.id-card.index');
    }

    /**
     * Department-filter step for teacher ID cards, mirroring the student
     * flow's class/section/group filter (studentIdCardPage()). Previously
     * this route rendered every teacher in the branch unconditionally, with
     * no filter step at all -- unlike students, which require picking a
     * class first, bounding the batch to a class size rather than the whole
     * branch's teacher roster.
     */
    public function teacherIdCardPage()
    {
        return view('backend.teachers.id-card.index', [
            'departments' => Department::where('institute_id', get_institute_id())
                ->where('branch_id', get_branch_id())
                ->orderBy('department_name')
                ->get(['id', 'department_name']),
        ]);
    }

    public function teacherIdCardList(Request $request)
    {
        $instituteId = (int) get_institute_id();
        $branchId = (int) get_branch_id();
        $departmentId = (int) $request->department_id;
        $validityDate = $request->validity;

        $query = Teacher::select(
            'teachers.*',
            'teachers.id AS id',
            'teachers.status AS teacher_status',
            'departments.department_name',
            'users.image as user_image',
            'users.name as user_name',
        )
            ->where('teachers.institute_id', $instituteId)
            ->where('teachers.branch_id', $branchId)
            ->join('users', 'users.id', '=', 'teachers.user_id')
            ->leftJoin('departments', 'departments.id', '=', 'teachers.department_id')
            ->orderBy('teachers.id', 'DESC');

        if ($departmentId > 0) {
            $query->where('teachers.department_id', $departmentId);
        }

        $teachers = $query->get();

        // Settings & signature
        $principalSignature = Signature::where('institute_id', $instituteId)
            ->where('branch_id', $branchId)
            ->first();
        $settings = Setting::where('institute_id', $instituteId)
            ->where('branch_id', $branchId)
            ->pluck('value', 'name')
            ->toArray();

        return view('backend.teachers.id-card.card-list', compact(
            'teachers',
            'principalSignature',
            'instituteId',
            'branchId',
            'settings',
            'validityDate'
        ));
    }

    /**
     * Department-filter step for staff ID cards. See teacherIdCardPage().
     */
    public function staffIdCardPage()
    {
        return view('backend.staffs.id-card.index', [
            'departments' => Department::where('institute_id', get_institute_id())
                ->where('branch_id', get_branch_id())
                ->orderBy('department_name')
                ->get(['id', 'department_name']),
        ]);
    }

    public function staffIdCardList(Request $request)
    {
        $instituteId = (int) get_institute_id();
        $branchId = (int) get_branch_id();
        $departmentId = (int) $request->department_id;
        $validityDate = $request->validity;

        $query = Staff::select(
            'staffs.*',
            'staffs.id AS id',
            'departments.department_name',
            'users.image as user_image',
            'users.name as user_name',
        )
            ->where('staffs.institute_id', $instituteId)
            ->where('staffs.branch_id', $branchId)
            ->join('users', 'users.id', '=', 'staffs.user_id')
            ->leftJoin('departments', 'departments.id', '=', 'staffs.department_id');

        if ($departmentId > 0) {
            $query->where('staffs.department_id', $departmentId);
        }

        $staffs = $query->get();

        // Settings & signature
        $principalSignature = Signature::where('institute_id', $instituteId)
            ->where('branch_id', $branchId)
            ->first();
        $settings = Setting::where('institute_id', $instituteId)
            ->where('branch_id', $branchId)
            ->pluck('value', 'name')
            ->toArray();

        return view('backend.staffs.id-card.card-list', compact(
            'staffs',
            'principalSignature',
            'instituteId',
            'branchId',
            'settings',
            'validityDate'
        ));
    }

    public function studentIdCardList(string $encodedData)
    {
        try {
            // Decode Base64
            $decoded = base64_decode($encodedData, true);

            if ($decoded === false) {
                return abort(404, 'Invalid request.');
            }

            $data = json_decode($decoded, true);

            if (! is_array($data)) {
                return abort(404, 'Invalid data format.');
            }

            // Every other controller handling class_id/section_id/group_id calls
            // assertClassSectionGroup() to confirm they belong to the caller's own
            // institute/branch; this one read them straight out of the decoded
            // payload with no such check. The student fetch below is still
            // correctly tenant-scoped (this never leaked another institute's
            // data), but an admin could still enumerate the class/section/group
            // ids of OTHER classes within their own branch by hand-editing the
            // decoded JSON before re-encoding it. Reading with isset() rather
            // than direct array access also means a malformed payload now falls
            // through to the existing 404 instead of a PHP warning.
            $class_id = isset($data['class_id']) ? (int) $data['class_id'] : 0;
            $section_id = isset($data['section_id']) ? (int) $data['section_id'] : 0;
            $group_id = isset($data['group_id']) ? (int) $data['group_id'] : 0;
            $validity_date = $data['validity'] ?? null;

            if (! $class_id) {
                return abort(404, 'Invalid data format.');
            }

            try {
                app(AcademicContextService::class)->assertClassSectionGroup(
                    $class_id,
                    $section_id ?: null,
                    $group_id ?: null,
                );
            } catch (ValidationException) {
                return abort(404, 'Invalid class, section, or group for your institute/branch.');
            }

            $instituteId = (int) get_institute_id();
            $branchId = (int) get_branch_id();

            // Fetch students. 0 means "no pagination" here — the underlying
            // service returns the plain (paginate($n) vs get()) collection based
            // on that — and this is a print page, not a browsable list: printing
            // "cards 1-200 of a 220-student class" silently dropped the last 20
            // students with no pagination control and no indication anything was
            // missing.
            $students = $this->studentService->getStudentsByClassSectionGroup(
                $class_id,
                $section_id,
                $group_id,
                0,
                $instituteId,
                $branchId
            );

            // Fetch settings
            $settings = Setting::where('institute_id', $instituteId)
                ->where('branch_id', $branchId)
                ->pluck('value', 'name')
                ->toArray();

            $principalSignature = Signature::where('institute_id', $instituteId)
                ->where('branch_id', $branchId)
                ->first();

            return view('backend.students.id-card.card-list', compact(
                'students',
                'validity_date',
                'principalSignature',
                'instituteId',
                'branchId',
                'settings'
            ));
        } catch (\Throwable $e) {
            // This blanket catch previously turned every genuine error -- a bad
            // query, a missing relation, an unrelated exception -- into an
            // identical bare 404 with no trace, which is nearly impossible to
            // debug in production. Logging keeps that same 404 response for the
            // requester (this is a public-ish print link; no reason to expose
            // internals) while leaving a record of what actually happened.
            Log::error('studentIdCardList failed: '.$e->getMessage(), ['exception' => $e]);

            return abort(404, 'Something went wrong.');
        }
    }

    public function instituteCacheClear()
    {
        clearInstituteHomepageCache(get_institute_id());

        Alert::toast('Institute Cache clear successful!', 'success');

        return redirect('dashboard');
    }
}
