<?php

namespace Modules\Student\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Models\ZoomMeeting;
use App\Services\AcademicContextService;
use App\Traits\StudentCollectionTrait;
use App\Traits\Trackable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Academic\Models\AbsentFine;
use Modules\Academic\Models\Assignment;
use Modules\Academic\Models\ClassModel;
use Modules\Academic\Models\ClassRoutine;
use Modules\Academic\Models\LibraryMember;
use Modules\Academic\Models\Section;
use Modules\Academic\Models\Student;
use Modules\Academic\Models\Subject;
use Modules\Academic\Models\Syllabus;
use Modules\Academic\Services\ClassService;
use Modules\Academic\Services\SectionService;
use Modules\Academic\Services\StudentService;
use Modules\Academic\Services\StudentSessionService;
use Modules\Academic\Services\UserService;
use Modules\Authentication\Models\User;
use Modules\Finance\Models\Fee;
use Modules\Finance\Models\StudentCollection;
use Modules\Finance\Models\StudentCollectionDetailsSubHead;
use Modules\GoogleMeet\Models\GoogleMeetMeeting;
use Modules\Hostel\Models\RoomMember;
use Modules\Library\Models\BookIssue;
use RealRashid\SweetAlert\Facades\Alert;

class StudentDashboardController extends Controller
{
    use StudentCollectionTrait, Trackable;

    public function __construct(
        private readonly UserService $userService,
        private readonly ClassService $classService,
        private readonly SectionService $sectionService,
        private readonly StudentService $studentService,
        private readonly StudentSessionService $studentSessionService,
    ) {}

    public function my_profile(): Factory|Redirector|RedirectResponse|View
    {
        $id = get_student_id();
        $student = $this->studentService->findStudentById($id);

        if (! $student) {
            Alert::error('Error', _lang('Something went wrong. Student not found.'));

            return redirect('student/my-profile');
        }

        $studentSession = app(AcademicContextService::class)->studentSessionForStudent((int) $id);
        $group_id = $studentSession?->group_id;
        $class_id = $studentSession?->class_id ?? 0;
        $elective_or_optional_subjects = Subject::getElectiveOrOptionalSubjects($class_id, $group_id);

        return view('student::profile', compact('student', 'elective_or_optional_subjects'));
    }

    public function qr_attendance(): Factory|View
    {
        $studentSession = app(AcademicContextService::class)->studentSessionForStudent((int) get_student_id());

        return view('student::qr_attendance', [
            'qrCode' => $studentSession?->qr_code,
            'className' => $studentSession?->class?->class_name,
            'sectionName' => $studentSession?->section?->section_name,
        ]);
    }

    public function my_subjects(): Factory|View
    {
        $student = app(AcademicContextService::class)->studentSessionForStudent((int) get_student_id());

        // Fetch compulsory subjects with group_id as null
        $compulsorySubjects = Subject::select('*', 'subjects.id AS id')
            ->join('classes', 'classes.id', '=', 'subjects.class_id')
            ->where('subjects.class_id', $student->class_id)
            ->whereNull('subjects.group_id')
            ->get();

        // Fetch subjects based on the student's class and group
        $studentSubjects = Subject::select('*', 'subjects.id AS id')
            ->join('classes', 'classes.id', '=', 'subjects.class_id')
            ->where('subjects.class_id', $student->class_id)
            ->where('subjects.group_id', $student->group_id)
            ->get();

        // Merge the two subject collections
        $subjects = $compulsorySubjects->merge($studentSubjects);

        return view('student::subject', compact('subjects'));
    }

    public function class_routine(): Factory|View
    {
        $student = app(AcademicContextService::class)->studentSessionForStudent((int) get_student_id());

        $data = [];
        $data['class'] = ClassModel::find($student->class_id);
        $data['section'] = Section::find($student->section_id);
        $data['routine'] = ClassRoutine::getRoutineView($student->class_id, $student->section_id);

        return view('student::class_routine', $data);
    }

    public function exam_routine(Request $request, $view = ''): Factory|View
    {
        $class_id = 0;
        $exam_id = '';

        if ($view == '') {
            return view('student::exam_routine', compact('class_id', 'exam_id'));
        } else {
            $student = app(AcademicContextService::class)->studentSessionForStudent((int) get_student_id());

            $data = [];
            $data['class_id'] = $student->class_id;
            $data['exam_id'] = $request->exam_id;
            $exam = $request->exam_id;

            // Fetch compulsory subjects with group_id as null
            $compulsorySubjects = Subject::select('*', 'subjects.id AS id')
                ->join('classes', 'classes.id', '=', 'subjects.class_id')
                ->where('subjects.class_id', $student->class_id)
                ->whereNull('subjects.group_id')
                ->get();

            // Fetch subjects based on the student's class and group
            $studentSubjects = Subject::select('*', 'subjects.id AS id')
                ->join('classes', 'classes.id', '=', 'subjects.class_id')
                ->where('subjects.class_id', $student->class_id)
                ->where('subjects.group_id', $student->group_id)
                ->get();

            // Merge the two subject collections
            $subjects = $compulsorySubjects->merge($studentSubjects);

            $data['subjects'] = $subjects;

            return view('student::exam_routine', $data);
        }
    }

    public function my_invoice_unpaid(): Factory|View
    {
        $student = app(AcademicContextService::class)->studentSessionForStudent((int) get_student_id());

        $year = 2024;
        $section = $student->section_id;

        $dues = Student::select(
            'students.id as student_id',
            'students.first_name',
            'student_sessions.roll',
            DB::raw('GROUP_CONCAT(
                DISTINCT
                CASE
                    WHEN (student_collection_details.total_payable - student_collection_details.total_paid) <> 0
                    THEN CONCAT(fee_heads.name, " : ", fee_sub_heads.name, "(", student_collection_details.total_paid - student_collection_details.total_payable, ")")
                    END
                    ORDER BY fee_sub_heads.id ASC
                    SEPARATOR ", "
                ) as due_details'),

            DB::raw('SUM(student_collection_details.fee_and_fine_payable - student_collection_details.fee_and_fine_paid) as total_fee_due'),
            DB::raw('SUM(student_collection_details.previous_due_payable - student_collection_details.previous_due_paid) as total_slip_due'),
            DB::raw('SUM(student_collection_details.total_payable - student_collection_details.total_paid) as total_due')
        )
            ->where('student_collection_details.total_payable', '>=', 'student_collection_details.total_paid')
            ->leftJoin('student_sessions', 'students.id', '=', 'student_sessions.student_id')
            ->leftJoin('student_collections', 'student_collections.student_id', '=', 'students.id')
            ->leftJoin('student_collection_details', 'student_collections.id', '=', 'student_collection_details.student_collection_id')
            ->leftJoin('fee_heads', 'student_collection_details.fee_head_id', '=', 'fee_heads.id')
            ->leftJoin('student_collection_details_sub_heads as scds', 'student_collection_details.id', '=', 'scds.student_collection_details_id')
            ->leftJoin('fee_sub_heads', 'scds.sub_head_id', '=', 'fee_sub_heads.id')
            ->whereYear('student_collections.invoice_date', $year)
            ->groupBy('students.id', 'student_sessions.roll');

        if (! empty($section)) {
            $dues->where('student_sessions.section_id', $section);
        }
        $unpaidInfos = $dues->get();

        return view('student::invoice.unpaid', compact('unpaidInfos'));
    }

    public function my_invoice_paid(): Factory|View
    {
        $student = app(AcademicContextService::class)->studentSessionForStudent((int) get_student_id());

        $year = 2024;
        $section_id = $student->section_id;
        $students = DB::table('students')
            ->select(
                'students.id as student_id',
                'student_sessions.roll as roll',
                DB::raw('CONCAT(students.first_name, " ", COALESCE(students.last_name, "")) as name'),
                DB::raw('(SELECT SUM(total_paid) FROM student_collections WHERE student_id = students.id) as `paid_amount`')
            )
            ->join('student_sessions', 'students.id', '=', 'student_sessions.student_id')
            ->join('student_collections', 'students.id', '=', 'student_collections.student_id')
            ->whereYear('student_collections.invoice_date', $year)
            ->orWhere('student_sessions.section_id', $section_id)
            ->groupBy('students.id')
            ->get();

        return view('student::invoice.paid', compact('students'));
    }

    public function library_history(): Factory|View
    {
        $member = LibraryMember::join('users', 'users.id', '=', 'library_members.user_id')->where('library_members.user_id', Auth::user()->id)->first();
        $issues = [];
        if ($member != null) {
            $issues = BookIssue::select('*', 'book_issues.id AS id')
                ->join('books', 'books.id', '=', 'book_issues.book_id')
                ->where('book_issues.library_id', $member->library_id)
                ->orderBy('book_issues.id', 'DESC')
                ->get();
        }

        return view('student::library_history', compact('issues', 'member'));
    }

    public function my_assignment(): Factory|View
    {
        $student = app(AcademicContextService::class)->studentSessionForStudent((int) get_student_id());

        $assignments = Assignment::select('*', 'assignments.id AS id')
            ->join('subjects', 'subjects.id', '=', 'assignments.class_id')
            ->where('assignments.class_id', $student->class_id)
            ->where('assignments.section_id', $student->section_id)
            ->where('assignments.session_id', get_option('academic_year'))
            ->where('assignments.institute_id', get_institute_id())
            ->where('assignments.branch_id', get_branch_id())
            ->orderBy('assignments.id', 'DESC')
            ->get();

        return view('student::assignments.assignment-list', compact('assignments'));
    }

    public function view_assignment($id): Factory|View|RedirectResponse
    {
        $student = app(AcademicContextService::class)->studentSessionForStudent((int) get_student_id());

        if (! $student) {
            return redirect('student/my-assignment')->with('error', _lang('No active academic session found for your account.'));
        }

        $assignment = Assignment::select('assignments.*', 'assignments.id AS id', 'classes.class_name', 'sections.section_name', 'subjects.subject_name', 'subjects.subject_code')
            ->join('classes', 'classes.id', '=', 'assignments.class_id')
            ->join('sections', 'sections.id', '=', 'assignments.section_id')
            ->join('subjects', 'subjects.id', '=', 'assignments.subject_id')
            ->where('assignments.id', $id)
            ->where('assignments.session_id', get_option('academic_year'))
            ->where('assignments.institute_id', get_institute_id())
            ->where('assignments.branch_id', get_branch_id())
            ->where('assignments.class_id', $student->class_id)
            ->where('assignments.section_id', $student->section_id)
            ->first();

        if (! $assignment) {
            return redirect('student/my-assignment')->with('error', _lang('Assignment not found.'));
        }

        return view('student::assignments.assignment-show', compact('assignment'));
    }

    public function my_syllabus(): Factory|View
    {
        $student = app(AcademicContextService::class)->studentSessionForStudent((int) get_student_id());

        $syllabus = Syllabus::select('*', 'syllabus.id AS id')
            ->join('classes', 'classes.id', '=', 'syllabus.class_id')
            ->where('syllabus.class_id', $student->class_id)
            ->where('syllabus.session_id', get_option('academic_year'))
            ->where('syllabus.institute_id', get_institute_id())
            ->where('syllabus.branch_id', get_branch_id())
            ->orderBy('syllabus.id', 'DESC')
            ->get();

        return view('student::syllabus.syllabus-list', compact('syllabus'));
    }

    public function view_syllabus($id): Factory|View
    {
        $student = app(AcademicContextService::class)->studentSessionForStudent((int) get_student_id());

        $syllabus = Syllabus::select('*', 'syllabus.id AS id')
            ->join('classes', 'classes.id', '=', 'syllabus.class_id')
            ->where('syllabus.class_id', $student->class_id)
            ->where('syllabus.id', $id)
            ->where('syllabus.session_id', get_option('academic_year'))
            ->where('syllabus.institute_id', get_institute_id())
            ->where('syllabus.branch_id', get_branch_id())
            ->first();

        return view('student::syllabus.syllabus-view', compact('syllabus'));
    }

    public function studentAttendanceFineReport(Request $request): Factory|View
    {
        $user = auth()->user();
        $class_id = $user->student->studentSession?->class_id;
        $section_id = $user->student->studentSession?->section_id;
        $student_id = $user->student->studentSession?->student_id;

        $attendanceAbsentFine = AbsentFine::where('period_id', 1)->first();
        $studentAttendances = DB::table('students')
            ->join(
                'student_attendances',
                'students.id',
                '=',
                'student_attendances.student_id'
            )
            ->join('student_sessions', 'student_sessions.student_id', '=', 'students.id')
            ->leftJoin('absent_fines', function ($join) {
                $join->on('absent_fines.class_id', '=', 'student_sessions.class_id')
                    ->where('absent_fines.period_id', 5);
            })
            ->select(
                'students.id as student_id',
                'students.first_name',
                'student_sessions.roll',
                'student_sessions.section_id',
                DB::raw('MONTH(student_attendances.date) as month'),
                DB::raw('COUNT(student_attendances.id) as absent_count'),
                DB::raw('SUM(absent_fines.fee_amount) as fine_amount')
            )
            ->whereYear('student_attendances.date', current_year())
            ->where('student_attendances.student_id', $student_id)
            ->where('student_attendances.class_id', $class_id)
            ->where('student_attendances.section_id', $section_id)
            ->where('student_attendances.attendance', 2)
            ->where('student_attendances.period_id', 5)
            ->groupBy('students.id', 'students.first_name', 'student_sessions.roll', 'student_sessions.section_id', 'month') // Added section_id and roll here
            ->orderBy('student_sessions.roll', 'ASC')
            ->orderBy('month', 'ASC')
            ->get();

        $processedData = [];
        foreach ($studentAttendances as $data) {
            $sectionData = $data->section_id;
            $sectionData = Section::where('id', $sectionData)->select('id', 'section_name')->first();
            $sectionName = $sectionData->section_name;
            $studentId = $data->student_id;
            $studentName = $data->first_name;
            $roll = $data->roll;
            $month = $data->month;
            $absentCount = $data->absent_count;
            $fineAmount = $data->fine_amount;

            if (! isset($processedData[$studentId])) {
                $processedData[$studentId] = [
                    'section_name' => $sectionName,
                    'student_name' => $studentName,
                    'roll' => $roll,
                    'months' => [],
                    'total_fine_amount' => 0,
                ];
            }

            if (! isset($processedData[$studentId]['months'][$month])) {
                $processedData[$studentId]['months'][$month] = [
                    'absent_count' => 0,
                    'fine_amount' => 0,
                ];
            }

            $processedData[$studentId]['months'][$month]['absent_count'] += $absentCount;
            $processedData[$studentId]['months'][$month]['fine_amount'] += $fineAmount;
            $processedData[$studentId]['total_fine_amount'] += $fineAmount;
            $processedData[$studentId]['attendance_fine'] = $attendanceAbsentFine->fee_amount;
        }

        return view('student::reports.student_attendance_fine_search_report', compact('class_id', 'section_id', 'processedData'));
    }

    public function studentQuizFineReport(Request $request): Factory|View
    {
        $user = auth()->user();
        $class_id = $user->student->studentSession?->class_id;
        $section_id = $user->student->studentSession?->section_id;
        $student_id = $user->student->studentSession?->student_id;

        $attendanceAbsentFine = AbsentFine::where('period_id', 6)->first();
        $studentAttendances = DB::table('students')
            ->join('student_attendances', 'students.id', '=', 'student_attendances.student_id')
            ->join('student_sessions', 'student_sessions.student_id', '=', 'students.id')
            ->leftJoin('absent_fines', function ($join) {
                $join->on('absent_fines.class_id', '=', 'student_sessions.class_id')
                    ->where('absent_fines.period_id', 6);
            })
            ->select(
                'students.id as student_id',
                'students.first_name',
                'student_sessions.roll',
                'student_sessions.section_id',
                DB::raw('MONTH(student_attendances.date) as month'),
                DB::raw('COUNT(student_attendances.id) as absent_count'),
                DB::raw('SUM(absent_fines.fee_amount) as fine_amount')
            )
            ->whereYear('student_attendances.date', current_year())
            ->where('student_attendances.student_id', $student_id)
            ->where('student_attendances.class_id', $class_id)
            ->where('student_attendances.section_id', $section_id)
            ->where('student_attendances.attendance', 2)
            ->where('student_attendances.period_id', 6)
            ->groupBy('students.id', 'students.first_name', 'student_sessions.roll', 'student_sessions.section_id', 'month') // Added section_id and roll here
            ->orderBy('student_sessions.roll', 'ASC')
            ->orderBy('month', 'ASC')
            ->get();

        $processedData = [];
        foreach ($studentAttendances as $data) {
            $sectionData = $data->section_id;
            $sectionData = Section::where('id', $sectionData)->select('id', 'section_name')->first();
            $sectionName = $sectionData->section_name;
            $studentId = $data->student_id;
            $studentName = $data->first_name;
            $roll = $data->roll;
            $month = $data->month;
            $absentCount = $data->absent_count;
            $fineAmount = $data->fine_amount;

            if (! isset($processedData[$studentId])) {
                $processedData[$studentId] = [
                    'section_name' => $sectionName,
                    'student_name' => $studentName,
                    'roll' => $roll,
                    'months' => [],
                    'total_fine_amount' => 0,
                ];
            }

            if (! isset($processedData[$studentId]['months'][$month])) {
                $processedData[$studentId]['months'][$month] = [
                    'absent_count' => 0,
                    'fine_amount' => 0,
                ];
            }

            $processedData[$studentId]['months'][$month]['absent_count'] += $absentCount;
            $processedData[$studentId]['months'][$month]['fine_amount'] += $fineAmount;
            $processedData[$studentId]['total_fine_amount'] += $fineAmount;
            $processedData[$studentId]['attendance_fine'] = $attendanceAbsentFine->fee_amount;
        }

        return view('student::reports.student_quiz_fine_search_report', compact('class_id', 'section_id', 'processedData'));
    }

    public function studentLabFineReport(Request $request): Factory|View
    {
        $user = auth()->user();
        $class_id = $user->student->studentSession?->class_id;
        $section_id = $user->student->studentSession?->section_id;
        $student_id = $user->student->studentSession?->student_id;

        $attendanceAbsentFine = AbsentFine::where('period_id', 7)->first();
        $studentAttendances = DB::table('students')
            ->join('student_attendances', 'students.id', '=', 'student_attendances.student_id')
            ->join('student_sessions', 'student_sessions.student_id', '=', 'students.id')
            ->leftJoin('absent_fines', function ($join) {
                $join->on('absent_fines.class_id', '=', 'student_sessions.class_id')
                    ->where('absent_fines.period_id', 7);
            })
            ->select(
                'students.id as student_id',
                'students.first_name',
                'student_sessions.roll',
                'student_sessions.section_id',
                DB::raw('MONTH(student_attendances.date) as month'),
                DB::raw('COUNT(student_attendances.id) as absent_count'),
                DB::raw('SUM(absent_fines.fee_amount) as fine_amount')
            )
            ->whereYear('student_attendances.date', current_year())
            ->where('student_attendances.student_id', $student_id)
            ->where('student_attendances.class_id', $class_id)
            ->where('student_attendances.section_id', $section_id)
            ->where('student_attendances.attendance', 2)
            ->where('student_attendances.period_id', 7)
            ->groupBy('students.id', 'students.first_name', 'student_sessions.roll', 'student_sessions.section_id', 'month') // Include roll and section_id in GROUP BY
            ->orderBy('student_sessions.roll', 'ASC')
            ->orderBy('month', 'ASC')
            ->get();

        $processedData = [];
        foreach ($studentAttendances as $data) {
            $sectionData = $data->section_id;
            $sectionData = Section::where('id', $sectionData)->select('id', 'section_name')->first();
            $sectionName = $sectionData->section_name;
            $studentId = $data->student_id;
            $studentName = $data->first_name;
            $roll = $data->roll;
            $month = $data->month;
            $absentCount = $data->absent_count;
            $fineAmount = $data->fine_amount;

            if (! isset($processedData[$studentId])) {
                $processedData[$studentId] = [
                    'section_name' => $sectionName,
                    'student_name' => $studentName,
                    'roll' => $roll,
                    'months' => [],
                    'total_fine_amount' => 0,
                ];
            }

            if (! isset($processedData[$studentId]['months'][$month])) {
                $processedData[$studentId]['months'][$month] = [
                    'absent_count' => 0,
                    'fine_amount' => 0,
                ];
            }

            $processedData[$studentId]['months'][$month]['absent_count'] += $absentCount;
            $processedData[$studentId]['months'][$month]['fine_amount'] += $fineAmount;
            $processedData[$studentId]['total_fine_amount'] += $fineAmount;
            $processedData[$studentId]['attendance_fine'] = $attendanceAbsentFine->fee_amount;
        }

        return view('student::reports.student_lab_fine_search_report', compact('class_id', 'section_id', 'processedData'));
    }

    public function getPaymentInfoStudent(Request $request): Factory|View
    {
        $user = auth()->user();
        $studentId = $user->student->id;
        $student = $user->student;

        // Fetch total paid amount for the current student
        $totalPaidAmount = DB::table('student_collections')
            ->where('student_id', $studentId)
            ->sum('total_paid');

        // Pass the total paid amount to the view
        return view('student::reports.student-payment-fee-info-list', compact('totalPaidAmount', 'student'));
    }

    public function getPaymentInfoDetails(int $id): Factory|View
    {
        // The {id} in the URL is ignored on purpose — a student may only ever
        // see their own collections. Previously this method fetched EVERY
        // student_collection in the tenant (no student_id filter at all),
        // leaking every family's fee history.
        $studentId = get_student_id();

        $studentCollections = StudentCollection::with([
            'student',
            'details' => function ($q) {
                return $q->with('feeHead', 'subHeads');
            },
        ])
            ->where('student_id', $studentId)
            ->get();

        return view('student::reports.payment-details-info', compact('studentCollections'));
    }

    public function getUnpaidFeeInfoStudent(Request $request): Factory|View
    {
        $user = auth()->user();
        $section = $user->student->studentSession->section_id;
        $feeHeads = [];

        $students = Student::query()
            ->select('users.*', 'student_sessions.roll', 'classes.class_name', 'sections.section_name', 'students.id as id', 'student_groups.group_name', 'students.student_category_id')
            ->join('users', 'users.id', '=', 'students.user_id')
            ->join('student_sessions', 'students.id', '=', 'student_sessions.student_id')
            ->leftJoin('classes', 'classes.id', '=', 'student_sessions.class_id')
            ->leftJoin('sections', 'sections.id', '=', 'student_sessions.section_id')
            ->leftJoin('student_groups', 'student_sessions.group_id', '=', 'student_groups.id')
            ->where('student_sessions.session_id', get_option('academic_year'))
            ->where('student_sessions.institute_id', get_institute_id())
            ->where('student_sessions.branch_id', get_branch_id())
            ->where('users.user_type', 'Student')
            ->where('student_sessions.section_id', $section)
            ->orderBy('student_sessions.roll', 'ASC')
            ->get();

        $studentDatas = [];
        foreach ($students as $s_key => $student) {
            if ($student->id === $user->student->id) {
                $id = $student->id;
                $session = app(AcademicContextService::class)->studentSessionForStudent((int) $id);
                $amountConfig = Fee::where('section_id', $session->section_id)
                    ->where('class_id', $session->class_id)
                    ->with('feeHead')
                    ->get();

                $total_due_amount = 0;
                foreach ($amountConfig as $config) {
                    $feeHeads[] = $config->feeHead;
                    $feeHeads = array_unique($feeHeads);
                }

                // Check the fee subheads if already added and remove them from the feeHeads array.
                foreach ($feeHeads as $key => $feeHead) {
                    $feeSubHeads = $feeHead->feeSubHeads;
                    $updatedFeeSubHeads = [];

                    $amount = [];
                    foreach ($feeSubHeads as $skey => $feeSubHead) {
                        $studentCollectionDetailsSubHead = StudentCollectionDetailsSubHead::where('student_id', $id)
                            ->where('session_id', $session->session_id)
                            ->where('fee_head_id', $feeHead->id)
                            ->where('sub_head_id', $feeSubHead->id)
                            ->first();

                        if (! $studentCollectionDetailsSubHead || $studentCollectionDetailsSubHead->collectionDetail->total_paid === 0) {
                            array_push(
                                $updatedFeeSubHeads,
                                $feeSubHead
                            );
                            $payable_amount = $this->getCollectionAmountsByFeeHeadAndSubHeads(
                                $id,
                                $feeHead->id,
                                (array) $feeSubHead->id
                            );
                            $amount[$feeSubHead->id] = $payable_amount;
                            $total_due_amount += $payable_amount['fee_and_fine_payable'];
                        }
                    }

                    $feeHeads[$key]->feeSubHeads = $updatedFeeSubHeads;
                    $feeHeads[$key]->amount = $amount;
                }
                $studentDatas[$s_key] = $student;
                $studentDatas[$s_key]['feeHeads'] = $feeHeads;
                $studentDatas[$s_key]['total_due_amount'] = $total_due_amount;
            }
        }

        return view('student::reports.student-unpaid-info', ['feeHeads' => $feeHeads, 'studentDatas' => $studentDatas]);
    }

    public function getStudentNotification(Request $request): Factory|View
    {
        if (isset($request->user_type)) {
            $users = User::where('user_type', $request->user_type)->where('user_status', '1')->get();

            return view('student::feedback.student-view', compact('users'));
        }

        return view('student::feedback.student-view');
    }

    public function getStudentZoom(): Factory|View
    {
        $student = auth()->user()->student;

        $class_id = $student?->studentSession?->class_id;
        $section_id = $student?->studentSession?->section_id;

        $meetings = ZoomMeeting::with([
            'teacher:id,name',
            'class:id,class_name',
            'section:id,section_name',
        ])
            ->where('class_id', $class_id)
            ->where('section_id', $section_id)
            ->latest()
            ->get();

        return view(
            'student::zoom.index',
            compact('meetings')
        );
    }

    public function getStudentGoogleMeet(): Factory|View
    {
        $student = auth()->user()->student;

        $class_id = $student?->studentSession?->class_id;
        $section_id = $student?->studentSession?->section_id;

        $meetings = GoogleMeetMeeting::with([
            'teacher:id,name',
            'classModel:id,class_name',
            'section:id,section_name',
            'subject:id,subject_name',
        ])
            ->where('class_id', $class_id)
            ->where('section_id', $section_id)
            ->whereJsonContains('visibility', 'student')
            ->orderByDesc('start_date')
            ->orderByDesc('start_time')
            ->get();

        return view(
            'student::google_meet.index',
            compact('meetings')
        );
    }

    public function getStudentHostelProfile(): Factory|View
    {
        $student = auth()->user()->student;

        $roomMember = RoomMember::with([
            'room.hostel',
            'room.building',
            'room.floor',
            'bed',
            'collections',
            'leaves',
        ])
            ->where('student_id', $student?->id)
            ->where('status', 'active')
            ->first();

        return view(
            'student::hostel.profile',
            compact('roomMember')
        );
    }
}
