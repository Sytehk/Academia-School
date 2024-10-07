<?php

namespace Modules\Student\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\AcademicContextService;
use App\Traits\StudentCollectionTrait;
use App\Traits\Trackable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Academic\Models\AbsentFine;
use Modules\Academic\Models\Assignment;
use Modules\Academic\Models\ClassModel;
use Modules\Academic\Models\ClassRoutine;
use Modules\Academic\Models\Event;
use Modules\Academic\Models\LibraryMember;
use Modules\Academic\Models\Notice;
use Modules\Academic\Models\Section;
use Modules\Academic\Models\Student;
use Modules\Academic\Models\Subject;
use Modules\Academic\Models\Syllabus;
use Modules\Academic\Services\ClassService;
use Modules\Academic\Services\SectionService;
use Modules\Academic\Services\StudentService;
use Modules\Academic\Services\StudentSessionService;
use Modules\Academic\Services\UserService;
use Modules\Finance\Models\Fee;
use Modules\Finance\Models\StudentCollectionDetailsSubHead;
use Modules\Library\Models\BookIssue;
use Modules\Student\Http\Requests\ProfileUpdateRequest;
use Modules\Teacher\Models\Behavior;
use Modules\Teacher\Models\ClassLesson;
use Modules\Teacher\Models\Gamification;
use Modules\Teacher\Models\Prayer;
use Modules\Teacher\Models\Resource;

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

    public function profileUpdate(ProfileUpdateRequest $request): JsonResponse
    {
        // Get only non-null fields from the request
        $data = array_filter($request->only([
            'first_name',
            'last_name',
            'father_name',
            'mother_name',
            'birthday',
            'gender',
            'blood_group',
            'religion',
            'phone',
            'address',
            'parent_id',
        ]), function ($value) {
            return ! is_null($value);
        });

        // Optionally cast parent_id to int if it's set
        if (isset($data['parent_id'])) {
            $data['parent_id'] = (int) $data['parent_id'];
        }

        // Update student with filtered data
        $this->studentService->updateStudent($data, get_student_id());

        return $this->responseSuccess([], 'Profile Update Successfully Done.');
    }

    public function my_profile(): JsonResponse
    {
        $id = get_student_id();

        $student = $this->studentService->findStudentById($id);

        if (! $student) {
            return $this->responseError([], _lang('Something went wrong. Student Profile not found.'));
        }

        return $this->responseSuccess($student, 'Student Profile fetch successfully.');
    }

    /**
     * The authenticated student's own QR code plus display-only info, for
     * showing in the mobile/API client so it can be scanned to mark
     * attendance. Always derived from the caller's own session — there is no
     * id parameter, so a student can never fetch another student's code.
     */
    public function qrAttendance(): JsonResponse
    {
        $studentSession = app(AcademicContextService::class)->studentSessionForStudent((int) get_student_id());

        if (! $studentSession || ! $studentSession->qr_code) {
            return $this->responseError([], 'No QR code has been generated for your account yet.', 404);
        }

        return $this->responseSuccess([
            'qr_code' => $studentSession->qr_code,
            'class_name' => $studentSession->class?->class_name,
            'section_name' => $studentSession->section?->section_name,
        ], 'QR code fetched successfully.');
    }

    public function my_subjects(): JsonResponse
    {
        $student = app(AcademicContextService::class)->studentSessionForStudent((int) get_student_id());

        // Fetch compulsory subjects with group_id as null
        $compulsorySubjects = Subject::select('subjects.id AS id', 'subjects.subject_name', 'subjects.subject_code', 'subjects.subject_type', 'subjects.class_id', 'subjects.group_id')
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

        if (! $subjects) {
            return $this->responseError([], _lang('Something went wrong. Subject not found.'));
        }

        return $this->responseSuccess($subjects, 'Student Subjects fetch successfully.');
    }

    public function class_routine(): JsonResponse
    {
        $student = app(AcademicContextService::class)->studentSessionForStudent((int) get_student_id());

        $data = [];
        $data['class'] = ClassModel::find($student->class_id);
        $data['section'] = Section::find($student->section_id);
        $data['routine'] = ClassRoutine::getRoutineView($student->class_id, $student->section_id);

        if (! $data) {
            return $this->responseError([], _lang('Something went wrong. Class Routine not found.'));
        }

        return $this->responseSuccess($data, 'Student Class Routines fetch successfully.');
    }

    public function exam_routine(Request $request): JsonResponse
    {
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

        if (! $data) {
            return $this->responseError([], _lang('Something went wrong. Exam Routine not found.'));
        }

        return $this->responseSuccess($data, 'Student Exam Routines fetch successfully.');
    }

    public function library_history(): JsonResponse
    {
        // Fetch the member
        $member = LibraryMember::join('users', 'users.id', '=', 'library_members.user_id')
            ->select('users.id', 'users.name', 'users.email', 'users.phone', 'users.user_type', 'users.image', 'users.image', 'library_members.*')
            ->where('library_members.user_id', Auth::user()->id)
            ->first();

        // Initialize the response data
        $data = null;
        if ($member != null) {
            // Fetch the issues and merge with the member's data
            $issues = BookIssue::select(
                'book_issues.id',
                'book_issues.library_id',
                'book_issues.issue_date',
                'book_issues.due_date',
                'book_issues.return_date',
                'book_issues.type',
                'books.id as book_id',
                'books.book_name',
                'books.code',
                'books.category',
            )
                ->join('books', 'books.id', '=', 'book_issues.book_id')
                ->where('book_issues.library_id', $member->library_id)
                ->orderBy('book_issues.id', 'DESC')
                ->get();

            // Convert member to array (if necessary) and add issues
            $data = $member->toArray();
            $data['issues'] = $issues->toArray();
        }

        if (! $data) {
            return $this->responseSuccess([], _lang('Library History not found.'));
        }

        return $this->responseSuccess($data, 'Student Library History fetched successfully.');
    }

    public function my_assignment(): JsonResponse
    {
        $student = app(AcademicContextService::class)->studentSessionForStudent((int) get_student_id());

        $assignments = Assignment::select('assignments.id', 'assignments.title', 'assignments.description', 'subjects.subject_name', 'subjects.subject_code', 'subjects.subject_type', 'assignments.deadline', 'assignments.file', 'assignments.file_2', 'assignments.file_3', 'assignments.file_4')
            ->join('subjects', 'subjects.id', '=', 'assignments.class_id')
            ->where('assignments.class_id', $student->class_id)
            ->where('assignments.section_id', $student->section_id)
            ->where('assignments.session_id', get_option('academic_year'))
            ->where('assignments.institute_id', get_institute_id())
            ->where('assignments.branch_id', get_branch_id())
            ->orderBy('assignments.id', 'DESC')
            ->get();

        if (! $assignments) {
            return $this->responseSuccess([], _lang('Something went wrong. Assignments not found.'));
        }

        return $this->responseSuccess($assignments, 'Student Assignments fetch successfully.');
    }

    public function view_assignment(int $id): JsonResponse
    {
        $student = app(AcademicContextService::class)->studentSessionForStudent((int) get_student_id());

        if (! $student) {
            return $this->responseError([], _lang('No active academic session found for your account.'), 404);
        }

        $assignment = Assignment::select('assignments.id', 'assignments.title', 'assignments.description', 'subjects.subject_name', 'subjects.subject_code', 'subjects.subject_type', 'assignments.deadline', 'assignments.file', 'assignments.file_2', 'assignments.file_3', 'assignments.file_4')
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
            return $this->responseError([], _lang('Something went wrong. Assignment not found.'));
        }

        return $this->responseSuccess($assignment, 'Student Assignment fetch successfully.');
    }

    public function my_syllabus(): JsonResponse
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

        if (! $syllabus) {
            return $this->responseError([], _lang('Something went wrong. Syllabus not found.'));
        }

        return $this->responseSuccess($syllabus, 'Student Syllabus fetch successfully.');
    }

    public function view_syllabus(int $id): JsonResponse
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

        if (! $syllabus) {
            return $this->responseError([], _lang('Something went wrong. Syllabus not found.'));
        }

        return $this->responseSuccess($syllabus, 'Student Syllabus fetch successfully.');
    }

    public function studentAttendanceFineReport(Request $request): JsonResponse
    {
        $user = auth()->user();
        $class_id = $user->student->studentSession?->class_id;
        $section_id = $user->student->studentSession?->section_id;
        $student_id = $user->student->id;

        $attendanceAbsentFine = AbsentFine::where('period_id', 1)->first();
        if (! $attendanceAbsentFine) {
            return $this->responseSuccess([], _lang('Attendance Fine not assign. please assign first'));
        }

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
            ->where('student_attendances.period_id', 1)
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

        if (! $processedData) {
            return $this->responseError([], _lang('Something went wrong. Attendance Fine Report not found.'));
        }

        return $this->responseSuccess($processedData, 'Student Attendance Fine Report fetch successfully.');
    }

    public function studentQuizFineReport(Request $request): JsonResponse
    {
        $user = auth()->user();
        $class_id = $user->student->studentSession?->class_id;
        $section_id = $user->student->studentSession?->section_id;
        $student_id = $user->student->studentSession?->student_id;

        $attendanceAbsentFine = AbsentFine::where('period_id', 2)->first();
        if (! $attendanceAbsentFine) {
            return $this->responseError([], _lang('Quiz Fine not assign. please assign first'), 422);
        }

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
            ->where('student_attendances.period_id', 2)
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

        if (! $processedData) {
            return $this->responseError([], _lang('Something went wrong. Attendance Quiz Report not found.'));
        }

        return $this->responseSuccess($processedData, 'Student Attendance Quiz Report fetch successfully.');
    }

    public function studentLabFineReport(Request $request): JsonResponse
    {
        $user = auth()->user();
        $class_id = $user->student->studentSession?->class_id;
        $section_id = $user->student->studentSession?->section_id;
        $student_id = $user->student->studentSession?->student_id;

        $attendanceAbsentFine = AbsentFine::where('period_id', 3)->first();
        if (! $attendanceAbsentFine) {
            return $this->responseError([], _lang('Lab Fine not assign. please assign first'), 422);
        }

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
            ->where('student_attendances.period_id', 1)
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

        if (! $processedData) {
            return $this->responseError([], _lang('Something went wrong. Attendance Lab Report not found.'));
        }

        return $this->responseSuccess($processedData, 'Student Attendance Lab Report fetch successfully.');
    }

    public function getPaymentInfoStudent(Request $request): JsonResponse
    {
        $user = auth()->user();
        $studentId = $user->student->id;
        $student = $user->student;

        // Fetch total paid amount for the current student
        $totalPaidAmount = DB::table('student_collections')
            ->where('student_id', $studentId)
            ->sum('total_paid');

        $data = [];
        $data['student'] = $student;
        $data['total_paid_amount'] = $totalPaidAmount;

        if (! $data) {
            return $this->responseError([], _lang('Something went wrong. Payment Info not found.'));
        }

        return $this->responseSuccess($data, 'Student Payment Info fetch successfully.');
    }

    public function getUnpaidFeeInfoStudent(Request $request): JsonResponse
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

        $data = [];
        $data['feeHeads'] = $feeHeads;
        $data['student_Data'] = $studentDatas;

        if (! $data) {
            return $this->responseError([], _lang('Something went wrong. Unpaid Info not found.'));
        }

        return $this->responseSuccess($data, 'Student Unpaid Info fetch successfully.');
    }

    public function studentNoticeGet(Request $request): JsonResponse
    {
        $perPage = (int) $request->per_page ?: 10;
        $notices = Notice::select('id', 'title', 'notice', 'date', 'created_by')->with('userType')->paginate($perPage);

        return $this->responseSuccess($notices, 'Notices fetch successfully.');
    }

    public function studentEventGet(Request $request): JsonResponse
    {
        $perPage = (int) $request->per_page ?: 10;
        $events = Event::select('id', 'start_date', 'end_date', 'name', 'details', 'location')->paginate($perPage);

        return $this->responseSuccess($events, 'Events fetch successfully.');
    }

    public function studentBehaviorGet(Request $request): JsonResponse
    {
        $perPage = (int) $request->per_page ?: 10;
        $events = Behavior::where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->paginate($perPage);

        return $this->responseSuccess($events, 'Behaviors fetch successfully.');
    }

    public function studentGamificationGet(Request $request): JsonResponse
    {
        $perPage = (int) $request->per_page ?: 10;
        $events = Gamification::where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->paginate($perPage);

        return $this->responseSuccess($events, 'Gamification fetch successfully.');
    }

    public function studentPrayerGet(Request $request): JsonResponse
    {
        $perPage = (int) $request->per_page ?: 10;
        $events = Prayer::where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->paginate($perPage);

        return $this->responseSuccess($events, 'Prayers fetch successfully.');
    }

    public function studentClassLessonGet(Request $request): JsonResponse
    {
        $perPage = (int) $request->per_page ?: 10;
        $events = ClassLesson::where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->paginate($perPage);

        return $this->responseSuccess($events, 'ClassLessons fetch successfully.');
    }

    public function studentResourcesGet(Request $request): JsonResponse
    {
        $perPage = (int) $request->per_page ?: 10;
        $events = Resource::where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->paginate($perPage);

        return $this->responseSuccess($events, 'Resources fetch successfully.');
    }
}
