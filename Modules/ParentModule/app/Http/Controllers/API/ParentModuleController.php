<?php

namespace Modules\ParentModule\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\AcademicContextService;
use App\Traits\StudentCollectionTrait;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Academic\Models\AbsentFine;
use Modules\Academic\Models\Assignment;
use Modules\Academic\Models\ClassModel;
use Modules\Academic\Models\ClassRoutine;
use Modules\Academic\Models\Event;
use Modules\Academic\Models\Exam;
use Modules\Academic\Models\ExamSchedule;
use Modules\Academic\Models\LibraryMember;
use Modules\Academic\Models\Notice;
use Modules\Academic\Models\Section;
use Modules\Academic\Models\Student;
use Modules\Academic\Models\StudentAttendance;
use Modules\Academic\Models\StudentSession;
use Modules\Academic\Models\Subject;
use Modules\Authentication\Models\User;
use Modules\Examination\Models\ClassExam;
use Modules\Examination\Services\ResultCardService;
use Modules\Examination\Services\ResultPublicationService;
use Modules\Finance\Models\Fee;
use Modules\Finance\Models\StudentCollectionDetailsSubHead;
use Modules\Library\Models\BookIssue;
use Modules\ParentModule\Models\ParentModel;
use Modules\Student\Models\AssignmentSubmit;
use Modules\Teacher\Models\Behavior;
use Modules\Teacher\Models\Gamification;
use Modules\Teacher\Models\Prayer;

class ParentModuleController extends Controller
{
    use StudentCollectionTrait;

    private function childSession(int $studentId): ?StudentSession
    {
        return app(AcademicContextService::class)->studentSessionForStudent($studentId);
    }

    public function myProfile(): JsonResponse
    {
        $parent = ParentModel::where('parent_models.id', get_parent_id())->with('user', 'student')->first();

        return $this->responseSuccess($parent, 'Profile fetch successfully.');
    }

    /**
     * Self-service profile update. Operates ONLY on the authenticated parent's
     * own row — there is no id in the request, so a parent can never touch
     * another parent's account (the admin APIParentController is separate and
     * back-office only).
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $parent = ParentModel::where('id', get_parent_id())->first();
        if (! $parent) {
            return $this->responseError([], _lang('Parent not found.'), 404);
        }

        $user = User::find($parent->user_id);
        if (! $user) {
            return $this->responseError([], _lang('Parent not found.'), 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'phone' => 'nullable|string|max:25',
            'email' => 'nullable|email|max:100|unique:users,email,'.$user->id,
            'parent_profession' => 'nullable|string|max:100',
            'present_address' => 'nullable|string|max:191',
            'permanent_address' => 'nullable|string|max:191',
            'image' => 'nullable|image|max:5120',
        ]);

        DB::beginTransaction();
        try {
            $parent->parent_name = $validated['name'];
            $parent->parent_profession = $validated['parent_profession'] ?? $parent->parent_profession;
            $parent->parent_phone = $validated['phone'] ?? $parent->parent_phone;
            $parent->present_address = $validated['present_address'] ?? $parent->present_address;
            $parent->permanent_address = $validated['permanent_address'] ?? $parent->permanent_address;

            $user->name = $validated['name'];
            if (array_key_exists('phone', $validated) && $validated['phone']) {
                $user->phone = $validated['phone'];
            }
            if (array_key_exists('email', $validated) && $validated['email']) {
                $user->email = $validated['email'];
            }
            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $user->image = fileUploader('users/', 'png', $request->file('image'), $user->image);
            }

            $parent->save();
            $user->save();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return $this->responseError([], _lang('Could not update your profile.'), 500);
        }

        return $this->responseSuccess($parent->fresh('user'), 'Profile updated successfully.');
    }

    /**
     * Self-service password change for the authenticated parent.
     */
    public function changePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = $request->user();
        if (! Hash::check($validated['current_password'], $user->password)) {
            return $this->responseError([], _lang('The current password is incorrect.'), 422);
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        // Also keep the legacy access_key mirror in sync.
        ParentModel::where('id', get_parent_id())->update(['access_key' => $validated['password']]);

        return $this->responseSuccess([], 'Password changed successfully.');
    }

    public function defaultChildGet(): JsonResponse
    {
        $parent = ParentModel::where('parent_models.id', get_parent_id())->with('student')->first();
        if (! $parent) {
            return $this->responseError([], _lang('Something went wrong. Parent not found.'), 404);
        }

        return $this->responseSuccess($parent->student, 'Default Child fetch successfully.');
    }

    public function myChildrenList(): JsonResponse
    {
        $childrenList = Student::where('students.parent_id', get_parent_id())
            ->join('student_sessions', 'students.id', '=', 'student_sessions.student_id')
            ->join('classes', 'classes.id', '=', 'student_sessions.class_id')
            ->join('sections', 'sections.id', '=', 'student_sessions.section_id')
            ->where('student_sessions.session_id', get_option('academic_year'))
            ->where('student_sessions.institute_id', get_institute_id())
            ->where('student_sessions.branch_id', get_branch_id())
            ->select('students.*', 'classes.class_name', 'sections.section_name')
            ->get();

        return $this->responseSuccess($childrenList, 'Children List fetch successfully.');
    }

    public function defaultChildAssign(Request $request): JsonResponse
    {
        $studentId = (int) $request->student_id;
        $parent = ParentModel::where('parent_models.id', get_parent_id())->first();
        if (! $parent) {
            return $this->responseError([], _lang('Something went wrong. Parent not found.'), 404);
        }

        // Only allow assigning a student who is actually this parent's child — without this
        // check any parent could submit an arbitrary student_id and gain read access to
        // another family's child data via every "my children" endpoint.
        $isOwnChild = Student::where('id', $studentId)->where('parent_id', $parent->id)->exists();
        if (! $isOwnChild) {
            return $this->responseError([], _lang('This student is not linked to your account.'), 403);
        }

        $parent->student_id = $studentId;
        $parent->save();

        return $this->responseSuccess($parent, 'Default Child Assign successfully.');
    }

    public function dashboardData(Request $request)
    {
        $studentId = (int) get_authenticated_parent_student_id();

        $studentSession = $this->childSession($studentId)?->load('class');

        if (! $studentSession) {
            return $this->responseError([], _lang('Something went wrong. Student not found.'), 404);
        }

        $classId = $studentSession->class_id;

        // Monthly Attendance
        $monthlyAttendance = StudentAttendance::where('student_id', $studentId)
            ->where('period_id', 1)
            ->whereBetween('date', [
                now()->startOfMonth()->toDateString(),
                now()->endOfMonth()->toDateString(),
            ])
            ->get();

        $monthlyTotal = $monthlyAttendance->count();
        $monthlyPresent = $monthlyAttendance->where('attendance', '1')->count();
        $monthlyPercentage = $monthlyTotal > 0 ? round(($monthlyPresent / $monthlyTotal) * 100, 2) : 0;

        // Overall Attendance
        $allAttendance = StudentAttendance::where('student_id', $studentId)
            ->where('period_id', 1)
            ->get();

        $totalAttendance = $allAttendance->count();
        $totalPresent = $allAttendance->where('attendance', '1')->count();
        $overallPercentage = $totalAttendance > 0 ? round(($totalPresent / $totalAttendance) * 100, 2) : 0;

        // Pending Assignments (class-wise)
        $pendingTasks = Assignment::where('class_id', $classId)
            ->count();

        // Upcoming Exams (class-wise)
        $upcomingExams = ExamSchedule::where('class_id', $classId)
            ->where('date', '>=', now()->toDateString())
            ->count();

        $data = [
            'attendance' => [
                'monthly_percentage' => $monthlyPercentage,
                'overall_percentage' => $overallPercentage,
            ],
            'tasks' => $pendingTasks,
            'upcoming_exams' => $upcomingExams,
        ];

        return $this->responseSuccess($data, 'Child overview data fetch successfully.');
    }

    public function myChildrenProfile(Request $request): JsonResponse
    {
        $studentId = (int) get_authenticated_parent_student_id();
        $student = Student::join('users', 'users.id', '=', 'students.user_id')
            ->join('student_sessions', 'students.id', '=', 'student_sessions.student_id')
            ->join('classes', 'classes.id', '=', 'student_sessions.class_id')
            ->join('sections', 'sections.id', '=', 'student_sessions.section_id')
            ->join('parent_models', 'parent_models.id', '=', 'students.parent_id')
            ->where('student_sessions.session_id', get_option('academic_year'))
            ->where('student_sessions.institute_id', get_institute_id())
            ->where('student_sessions.branch_id', get_branch_id())
            ->where('students.parent_id', get_parent_id())
            ->where('students.id', $studentId)->first();

        return $this->responseSuccess($student, 'Children Profile fetch successfully.');
    }

    public function childrenAttendance(Request $request): JsonResponse
    {
        $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2000|max:2100',
        ]);

        $studentId = (int) get_authenticated_parent_student_id();
        $month = (int) $request->month;
        $year = (int) $request->year;

        $num_of_days = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        $attendances = DB::table('student_attendances')
            ->select('student_attendances.*')
            ->join('students', 'student_attendances.student_id', '=', 'students.id')
            ->where('student_attendances.student_id', $studentId)
            ->where('students.parent_id', get_parent_id())
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderBy('date', 'asc')
            ->get();

        $report_data = [];
        $attendance_value = [
            '0' => '',
            '1' => _lang('Present'),
            '2' => _lang('Absent'),
            '3' => _lang('Late'),
        ];

        // Build date => row map (status + check-in/out — null for manual/
        // historical records and any day with no attendance row at all).
        $attendanceMap = [];
        foreach ($attendances as $attendance) {
            $attendanceMap[$attendance->date] = [
                'status' => $attendance_value[$attendance->attendance] ?? '',
                'check_in_at' => $attendance->check_in_at,
                'check_out_at' => $attendance->check_out_at,
            ];
        }

        // Convert to structured day list
        $days = [];
        for ($day = 1; $day <= $num_of_days; $day++) {
            $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $row = $attendanceMap[$date] ?? null;
            $days[] = [
                'date' => $date,
                'day' => date('l', strtotime($date)), // Full weekday name
                'status' => $row['status'] ?? '',
                'check_in_at' => $row['check_in_at'] ?? null,
                'check_out_at' => $row['check_out_at'] ?? null,
            ];
        }

        return response()->json([
            'month' => [
                'month' => $month,
                'year' => $year,
            ],
            'days' => $days,
        ]);
    }

    public function mySubjects(Request $request): JsonResponse
    {
        $studentId = (int) get_authenticated_parent_student_id();
        $student = $this->childSession($studentId);
        if (! $student) {
            return $this->responseError([], _lang('Something went wrong. Student not found.'), 404);
        }

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

    public function classRoutine(Request $request): JsonResponse
    {
        $studentId = (int) get_authenticated_parent_student_id();
        $student = $this->childSession($studentId);
        if (! $student) {
            return $this->responseError([], _lang('Something went wrong. Student not found.'), 404);
        }

        $data = [];
        $data['class'] = ClassModel::find($student->class_id);
        $data['section'] = Section::find($student->section_id);
        $data['routine'] = ClassRoutine::getRoutineView($student->class_id, $student->section_id);

        if (! $data) {
            return $this->responseError([], _lang('Something went wrong. Class Routine not found.'));
        }

        return $this->responseSuccess($data, 'Student Class Routines fetch successfully.');
    }

    public function examRoutine(Request $request): JsonResponse
    {
        $studentId = (int) get_authenticated_parent_student_id();

        $studentSession = $this->childSession($studentId)?->load('student');

        if (! $studentSession || ! $studentSession->student) {
            return $this->responseError([], _lang('Something went wrong. Student not found.'), 404);
        }

        $classId = $studentSession->class_id;
        $studentGroupId = $studentSession->group_id;

        // Fetch all exams
        $exams = Exam::all();

        // Fetch compulsory subjects (group_id null)
        $compulsorySubjects = Subject::where('class_id', $classId)
            ->whereNull('group_id');

        // Fetch group-specific subjects
        $groupSubjects = Subject::where('class_id', $classId)
            ->where('group_id', $studentGroupId);

        // Merge query results with union
        $subjectsQuery = $compulsorySubjects->union($groupSubjects);

        // Fetch all exam schedules for these subjects (for any exam)
        $subjectsWithSchedules = DB::table(DB::raw("({$subjectsQuery->toSql()}) as subjects"))
            ->mergeBindings($subjectsQuery->getQuery()) // bind params
            ->leftJoin('exam_schedules', 'subjects.id', '=', 'exam_schedules.subject_id')
            ->select(
                'subjects.id as subject_id',
                'subjects.subject_name',
                'subjects.subject_code',
                'subjects.subject_type',
                'subjects.class_id',
                'subjects.group_id',
                'exam_schedules.id as schedule_id',
                'exam_schedules.exam_id',
                'exam_schedules.date',
                'exam_schedules.start_time',
                'exam_schedules.end_time',
                'exam_schedules.room',
            )
            ->get();

        // Group schedules by exam
        $examSchedulesGrouped = [];

        foreach ($exams as $exam) {
            $examSchedulesGrouped[$exam->id] = [
                'exam' => $exam,
                'subjects' => $subjectsWithSchedules->where('exam_id', $exam->id)->values(),
            ];
        }

        // If you want to return as array (not keyed by exam id)
        $response = array_values($examSchedulesGrouped);

        return $this->responseSuccess($response, _lang('Student Exam Routines fetched successfully.'));
    }

    public function myAssignment(Request $request): JsonResponse
    {
        $studentId = (int) get_authenticated_parent_student_id();
        $student = $this->childSession($studentId);
        if (! $student) {
            return $this->responseError([], _lang('Something went wrong. Student not found.'), 404);
        }

        $assignments = Assignment::select('assignments.id', 'assignments.title', 'assignments.description', 'subjects.subject_name', 'subjects.subject_code', 'subjects.subject_type', 'assignments.deadline', 'assignments.file', 'assignments.file_2', 'assignments.file_3', 'assignments.file_4')
            ->join('subjects', 'subjects.id', '=', 'assignments.class_id')
            ->where('assignments.class_id', $student->class_id)
            ->where('assignments.section_id', $student->section_id)
            ->where('assignments.session_id', get_option('academic_year'))
            ->where('assignments.institute_id', get_institute_id())
            ->where('assignments.branch_id', get_branch_id())
            ->orderBy('assignments.id', 'DESC')
            ->paginate($request->per_page ?: 10);

        if (! $assignments) {
            return $this->responseError([], _lang('Something went wrong. Assignments not found.'));
        }

        return $this->responseSuccess($assignments, 'Student Assignments fetch successfully.');
    }

    public function assignmentSubmit(Request $request): JsonResponse
    {
        $studentId = (int) get_authenticated_parent_student_id();
        $student = $this->childSession($studentId);
        if (! $student) {
            return $this->responseError([], _lang('Something went wrong. Student not found.'), 404);
        }

        $assignmentSubmits = AssignmentSubmit::select(
            'assignment_submits.*',
            'assignments.title AS assignment_title',
            'assignments.description AS assignment_description',
            'assignments.deadline AS assignment_deadline',
            'classes.class_name',
            'sections.section_name',
            'subjects.subject_name'
        )
            ->join('assignments', 'assignments.id', '=', 'assignment_submits.assignment_id') // Link to assignments
            ->join('classes', 'classes.id', '=', 'assignments.class_id') // Link to classes
            ->join('sections', 'sections.id', '=', 'assignments.section_id') // Link to sections
            ->join('subjects', 'subjects.id', '=', 'assignments.subject_id') // Link to subjects
            ->where('assignment_submits.student_id', $studentId)
            ->where('assignments.session_id', get_option('academic_year'))
            ->where('assignments.institute_id', get_institute_id())
            ->where('assignments.branch_id', get_branch_id()) // Ensure correct academic year
            ->orderBy('assignment_submits.id', 'DESC')
            ->paginate($request->per_page ?: 10);

        if (! $assignmentSubmits) {
            return $this->responseError([], _lang('Something went wrong. Assignment Submit not found.'));
        }

        return $this->responseSuccess($assignmentSubmits, 'Assignment Submit fetch successfully.');
    }

    public function studentAttendanceFineReport(Request $request): JsonResponse
    {
        // Step 1: Get authenticated student ID (parent or student context)
        $studentId = (int) get_authenticated_parent_student_id();
        // Step 2: Fetch student session
        $studentSession = $this->childSession($studentId)?->load('student');
        if (! $studentSession || ! $studentSession->student) {
            return $this->responseSuccess([], _lang('Student session or student record not found.'));
        }

        // Step 3: Required IDs
        $classId = $studentSession->class_id;
        $sectionId = $studentSession->section_id;

        // Step 4: Get base fine value
        $attendanceFine = AbsentFine::where('period_id', 1)->first();
        if (! $attendanceFine) {
            return $this->responseSuccess([], _lang('Attendance fine not assigned. Please assign it first.'));
        }

        $attendanceFineAmount = (float) $attendanceFine->fee_amount;

        // Step 5: Query attendance with absent counts
        $attendances = DB::table('student_attendances')
            ->join('student_sessions', 'student_sessions.student_id', '=', 'student_attendances.student_id')
            ->where('student_attendances.student_id', $studentId)
            ->where('student_attendances.class_id', $classId)
            ->where('student_attendances.section_id', $sectionId)
            ->where('student_attendances.attendance', 2) // Absent
            ->where('student_attendances.period_id', 1)
            ->whereYear('student_attendances.date', current_year())
            ->select(
                DB::raw('MONTH(student_attendances.date) as month'),
                DB::raw('COUNT(student_attendances.id) as absent_count')
            )
            ->groupBy('month')
            ->orderBy('month', 'ASC')
            ->get();

        // Step 6: Format results
        $months = [];
        $totalFineAmount = 0;

        foreach ($attendances as $record) {
            $month = (int) $record->month;
            $absentCount = (int) $record->absent_count;
            $fineAmount = $absentCount * $attendanceFineAmount;

            $months[] = [
                'month' => $month,
                'month_name' => Carbon::create()->month($month)->format('F'),
                'absent_count' => $absentCount,
                'fine_amount' => number_format($fineAmount, 2),
            ];

            $totalFineAmount += $fineAmount;
        }

        // Step 7: Final response structure
        $responseData = [
            'student_name' => $studentSession->student->first_name ?? 'N/A',
            'roll' => $studentSession->roll ?? '',
            'section_name' => Section::where('id', $sectionId)->value('section_name') ?? '',
            'attendance_fine' => number_format($attendanceFineAmount, 2),
            'total_fine_amount' => number_format($totalFineAmount, 2),
            'months' => $months,
        ];

        return $this->responseSuccess($responseData, 'Student Attendance Fine Report fetched successfully.');
    }

    public function getPaymentInfoStudent(Request $request): JsonResponse
    {
        $studentId = (int) get_authenticated_parent_student_id();
        $studentSession = $this->childSession($studentId)?->load('student');

        if (! $studentSession || ! $studentSession->student) {
            return $this->responseError([], _lang('Something went wrong. Student not found.'), 404);
        }

        // Fetch student info
        $student = $studentSession->student;

        // Total paid amount from student_collections
        $collectionsQuery = DB::table('student_collections')
            ->where('student_id', $studentId)
            ->where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id());

        if ($sessionId = get_option('academic_year')) {
            $collectionsQuery->where('session_id', $sessionId);
        }

        $totalPaidAmount = $collectionsQuery->sum('total_paid');

        // Collection history from student_collection_details
        $collectionDetailsQuery = DB::table('student_collection_details as scd')
            ->join('student_collections as sc', 'sc.id', '=', 'scd.student_collection_id')
            ->leftJoin('fee_heads as fh', 'fh.id', '=', 'scd.fee_head_id')
            ->select(
                'sc.invoice_id',
                'sc.invoice_date',
                'fh.name as fee_head_name',
                'scd.fee_and_fine_paid',
                'scd.waiver',
                'scd.fine_payable',
                'scd.previous_due_paid'
            )
            ->where('sc.student_id', $studentId)
            ->where('sc.institute_id', get_institute_id())
            ->where('sc.branch_id', get_branch_id());

        if ($sessionId = get_option('academic_year')) {
            $collectionDetailsQuery->where('sc.session_id', $sessionId);
        }

        $collectionDetails = $collectionDetailsQuery
            ->orderByDesc('sc.invoice_date')
            ->get()
            ->map(function ($row) {
                return [
                    'invoice_id' => $row->invoice_id,
                    'invoice_date' => Carbon::parse($row->invoice_date)->format('d M, Y'),
                    'fee_head_name' => $row->fee_head_name,
                    'paid_amount' => number_format($row->fee_and_fine_paid, 2),
                    'waiver' => number_format($row->waiver, 2),
                    'fine_amount' => number_format($row->fine_payable, 2),
                    'previous_due_paid' => number_format($row->previous_due_paid, 2),
                ];
            });

        // Response payload
        $data = [
            'student' => [
                'name' => $student->first_name.' '.$student->last_name,
                'roll' => $studentSession->roll,
                'class_id' => $studentSession->class_id,
                'section_id' => $studentSession->section_id,
            ],
            'total_paid_amount' => number_format($totalPaidAmount, 2),
            'collection_history' => $collectionDetails,
        ];

        return $this->responseSuccess($data, 'Student Payment Info fetched successfully.');
    }

    public function getUnpaidFeeInfoStudent(Request $request): JsonResponse
    {
        $studentId = (int) get_authenticated_parent_student_id();

        $studentSession = $this->childSession($studentId)?->load(['student.user', 'class', 'section', 'group']);

        if (! $studentSession || ! $studentSession->student) {
            return $this->responseError([], _lang('Something went wrong. Student not found.'), 404);
        }

        $student = $studentSession->student;
        $sessionId = $studentSession->session_id;
        $classId = $studentSession->class_id;
        $sectionId = $studentSession->section_id;

        // Fetch fee configurations for this class/section
        $amountConfigs = Fee::with('feeHead.feeSubHeads')
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->get();

        $feeHeadsData = [];
        $totalDueAmount = 0;

        foreach ($amountConfigs as $config) {
            $feeHead = $config->feeHead;
            $feeHeadId = $feeHead->id;
            $subHeads = $feeHead->feeSubHeads;

            foreach ($subHeads as $subHead) {
                $collectionSub = StudentCollectionDetailsSubHead::where('student_id', $studentId)
                    ->where('session_id', $sessionId)
                    ->where('fee_head_id', $feeHeadId)
                    ->where('sub_head_id', $subHead->id)
                    ->first();

                if (! $collectionSub || optional($collectionSub->collectionDetail)->total_paid == 0) {
                    $payable = $this->getCollectionAmountsByFeeHeadAndSubHeads(
                        $studentId,
                        $feeHeadId,
                        [$subHead->id]
                    );

                    // Initialize fee head entry if not already set
                    if (! isset($feeHeadsData[$feeHeadId])) {
                        $feeHeadsData[$feeHeadId] = [
                            'fee_head_id' => $feeHeadId,
                            'fee_head_name' => $feeHead->name,
                            'sub_heads' => [],
                        ];
                    }

                    $feeHeadsData[$feeHeadId]['sub_heads'][] = [
                        'sub_head_id' => $subHead->id,
                        'sub_head_name' => $subHead->name,
                        'fee_and_fine_payable' => number_format($payable['fee_and_fine_payable'], 2),
                    ];

                    $totalDueAmount += $payable['fee_and_fine_payable'];
                }
            }
        }

        // Convert associative to indexed array
        $feeHeadsData = array_values($feeHeadsData);

        // Build final student data structure
        $studentData = [
            'id' => $student->id,
            'name' => $student->user?->name ?? '',
            'email' => $student->user?->email ?? '',
            'phone' => $student->user?->phone ?? '',
            'roll' => $studentSession->roll,
            'class_name' => $studentSession->class?->class_name ?? '',
            'section_name' => $studentSession->section?->section_name ?? '',
            'group_name' => $studentSession->group?->group_name ?? '',
            'student_category_id' => $student->student_category_id,
            'fee_heads' => $feeHeadsData,
            'total_due_amount' => number_format($totalDueAmount, 2),
        ];

        return $this->responseSuccess([
            'student_data' => $studentData,
        ], 'Student Unpaid Info fetched successfully.');
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
        $studentId = (int) get_authenticated_parent_student_id();

        $perPage = (int) $request->per_page ?: 10;
        $events = Behavior::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->where('student_id', $studentId)
            ->paginate($perPage);

        return $this->responseSuccess($events, 'Behaviors fetch successfully.');
    }

    public function studentGamificationGet(Request $request): JsonResponse
    {
        $studentId = (int) get_authenticated_parent_student_id();

        $perPage = (int) $request->per_page ?: 10;
        $events = Gamification::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->where('student_id', $studentId)
            ->paginate($perPage);

        return $this->responseSuccess($events, 'Gamification fetch successfully.');
    }

    public function subjectList(): JsonResponse
    {
        $studentId = (int) get_authenticated_parent_student_id();

        $studentSession = $this->childSession($studentId);
        if (! $studentSession) {
            return $this->responseError([], _lang('Something went wrong. Student not found.'), 404);
        }

        $group = $studentSession->group_id;

        // Fetch compulsory subjects
        $compulsorySubjects = Subject::select(
            'subjects.id AS id',
            'subjects.subject_name',
            'subjects.subject_code',
            'subjects.subject_type',
            'subjects.class_id',
            'subjects.group_id'
        )
            ->where('subjects.class_id', $studentSession->class_id)
            ->whereNull('subjects.group_id')
            ->get();

        // Fetch group-based subjects
        $groupSubjects = Subject::select(
            'subjects.id AS id',
            'subjects.subject_name',
            'subjects.subject_code',
            'subjects.subject_type',
            'subjects.class_id',
            'subjects.group_id'
        )
            ->where('subjects.class_id', $studentSession->class_id)
            ->where('subjects.group_id', $group)
            ->get();

        // Merge results
        $subjects = $compulsorySubjects->merge($groupSubjects);

        if ($subjects->isEmpty()) {
            return $this->responseError([], _lang('Something went wrong. Subject not found.'));
        }

        return $this->responseSuccess($subjects, 'Student Subjects fetched successfully.');
    }

    public function studentPrayerGet(Request $request): JsonResponse
    {
        $studentId = (int) get_authenticated_parent_student_id();
        $studentSession = $this->childSession($studentId);
        if (! $studentSession) {
            return $this->responseError([], _lang('Something went wrong. Student not found.'), 404);
        }

        $student = $studentSession->student;
        $perPage = (int) $request->per_page ?: 10;
        $events = Prayer::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->where('user_id', $student->user?->id)
            ->paginate($perPage);

        return $this->responseSuccess($events, 'Prayers fetch successfully.');
    }

    public function examList(Request $request): JsonResponse
    {
        $studentId = (int) get_authenticated_parent_student_id();
        $studentSession = $this->childSession($studentId);
        if (! $studentSession) {
            return $this->responseError([], _lang('Something went wrong. Student not found.'), 404);
        }

        // Check for existing assignments in a single query
        $existingExams = ClassExam::where('class_id', $studentSession->class_id)
            ->with('exam', 'class', 'meritType')
            ->get();

        return $this->responseSuccess($existingExams, 'Exams fetch successfully.');
    }

    /**
     * The child's term result, computed by the same engine that prints the
     * official result card (ResultCardService), so the parent app can never show
     * different numbers from the printed card. The legacy top-level keys are
     * kept for existing app builds; `result_card` carries the full card.
     */
    public function examResult(Request $request): JsonResponse
    {
        $request->validate(['exam_id' => 'required|integer|tenant_exists:exams,id']);

        $studentId = (int) get_authenticated_parent_student_id();
        $examId = (int) $request->exam_id;

        $studentSession = $this->childSession($studentId)?->load(['student.user', 'session', 'class', 'section', 'group']);

        if (! $studentSession) {
            return $this->responseError([], _lang('Student session not found.'), 404);
        }

        if (! app(ResultPublicationService::class)->isPublished($examId, (int) $studentSession->class_id)) {
            return $this->responseSuccess([], _lang('Results for this exam have not been published yet.'));
        }

        $card = app(ResultCardService::class)->singleTermCard($studentSession, $examId);
        $result = $card['result'];
        $subjectsWithMarks = collect($result['subjects'])->filter(fn ($row) => $row['obtained'] !== null);

        if ($subjectsWithMarks->isEmpty()) {
            return $this->responseSuccess([], _lang('No exam results found.'));
        }

        $response = [
            'student' => [
                'id' => $studentSession->student->student_id ?? $studentSession->student_id,
                'name' => $result['student_name'],
                'class' => $result['class_name'] ?? 'N/A',
                'section' => $result['section_name'] ?? 'N/A',
                'roll' => $studentSession->roll,
            ],
            'exam' => [
                'name' => $card['exam_name'],
                'year' => $result['session_name'],
                'total_marks' => $result['total_full_marks'],
                'obtained_marks' => $result['total_marks'],
                'percentage' => $result['percentage'],
                'grade' => $result['grade'],
                'grade_point' => $result['grade_point'],
                'result' => $result['status'] === 'pass' ? 'Pass' : 'Fail',
            ],
            'subjects' => $subjectsWithMarks->map(fn ($row) => [
                'name' => $row['subject_name'],
                'full_marks' => $row['full_marks'],
                'marks_obtained' => $row['obtained'],
                'grade' => $row['grade'],
                'remarks' => $row['remarks'],
            ])->values(),
            'position_in_class' => $result['position'],
            'teacher_remarks' => $result['teacher_remarks'],
            'issue_date' => now()->toDateString(),
            'result_card' => $card,
        ];

        return $this->responseSuccess($response, 'Progress report fetched successfully.');
    }

    public function libraryHistory(Request $request): JsonResponse
    {
        $studentId = get_authenticated_parent_student_id();

        // Fetch the member by student ID
        $member = LibraryMember::join('users', 'users.id', '=', 'library_members.user_id')
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'users.phone',
                'users.user_type',
                'users.image',
                'library_members.*'
            )
            ->where('library_members.student_id', $studentId)
            ->first();

        if (! $member) {
            return $this->responseError([], _lang('Library member not found.'));
        }

        // Fetch the book issues for the student
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
            'books.category'
        )
            ->join('books', 'books.id', '=', 'book_issues.book_id')
            ->where('book_issues.student_id', $studentId)
            ->orderByDesc('book_issues.id')
            ->get();

        // Prepare response
        $data = $member->toArray();
        $data['issues'] = $issues;

        return $this->responseSuccess($data, 'Student Library History fetched successfully.');
    }
}
