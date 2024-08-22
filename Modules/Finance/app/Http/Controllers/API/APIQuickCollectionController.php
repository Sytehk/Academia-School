<?php

namespace Modules\Finance\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\NotificationDispatchService;
use App\Traits\StudentCollectionTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Academic\Models\AbsentFine;
use Modules\Academic\Models\Student;
use Modules\Academic\Models\StudentSession;
use Modules\Academic\Services\StudentService;
use Modules\Academic\Services\StudentSessionService;
use Modules\Accounting\Models\AccountingLedger;
use Modules\Finance\Http\Requests\StudentCollectionCreateRequest;
use Modules\Finance\Models\AttendanceFine;
use Modules\Finance\Models\AttendanceWaiver;
use Modules\Finance\Models\Fee;
use Modules\Finance\Models\StudentCollection;
use Modules\Finance\Models\StudentCollectionDetailsSubHead;

class APIQuickCollectionController extends Controller
{
    use StudentCollectionTrait;

    public function __construct(
        private readonly StudentService $studentService,
        private readonly StudentSessionService $studentSessionService,
        private readonly NotificationDispatchService $notificationDispatch
    ) {}

    public function index(Request $request): JsonResponse
    {
        $sectionId = intval($request->section_id);
        $classId = intval($request->class_id);
        $perPage = intval($request->perPage);

        $students = Student::query()
            ->select('users.*', 'student_sessions.roll', 'classes.class_name', 'sections.section_name', 'students.id as id', 'student_groups.group_name', 'students.student_category_id', 'students.status as student_status')
            ->join('users', 'users.id', '=', 'students.user_id')
            ->join('student_sessions', 'students.id', '=', 'student_sessions.student_id')
            ->leftJoin('classes', 'classes.id', '=', 'student_sessions.class_id')
            ->leftJoin('sections', 'sections.id', '=', 'student_sessions.section_id')
            ->leftJoin('student_groups', 'student_sessions.group_id', '=', 'student_groups.id')
            ->where('student_sessions.session_id', get_option('academic_year'))
            ->where('student_sessions.institute_id', get_institute_id())
            ->where('student_sessions.branch_id', get_branch_id())
            ->where('users.user_type', 'Student')
            ->where('student_sessions.section_id', $sectionId)
            ->where('student_sessions.class_id', $classId)
            ->orderBy('student_sessions.roll', 'ASC')
            ->paginate($perPage);

        $students = Student::query()
            ->select('students.id as id', 'student_sessions.roll', 'students.first_name as name', 'classes.class_name', 'sections.section_name', 'student_groups.group_name')
            ->join('users', 'users.id', '=', 'students.user_id')
            ->join('student_sessions', 'students.id', '=', 'student_sessions.student_id')
            ->leftJoin('classes', 'classes.id', '=', 'student_sessions.class_id')
            ->leftJoin('sections', 'sections.id', '=', 'student_sessions.section_id')
            ->leftJoin('student_groups', 'student_sessions.group_id', '=', 'student_groups.id')
            ->where('student_sessions.session_id', get_option('academic_year'))
            ->where('student_sessions.institute_id', get_institute_id())
            ->where('student_sessions.branch_id', get_branch_id())
            ->where('users.user_type', 'Student')
            ->where('student_sessions.section_id', $sectionId)
            ->where('student_sessions.class_id', $classId)
            ->orderBy('student_sessions.roll', 'ASC')
            ->paginate($perPage);

        $data = [
            'sectionId' => $sectionId,
            'classId' => $classId,
            'students' => $students,
        ];

        return $this->responseSuccess($data, 'Quick Collection Fetch Successfully.');
    }

    public function store(StudentCollectionCreateRequest $request): JsonResponse
    {
        try {
            $studentCollection = $this->createCollection(
                $request->all()
            );

            $student = Student::where('id', $studentCollection->student_id)->first();
            $channel = $this->notificationDispatch->resolveChannel($request->notify_channel, $request->sms_status);
            if ($student && $channel !== NotificationDispatchService::CHANNEL_NONE) {
                try {
                    $mobile_number = $this->notificationDispatch->studentPhone($student);
                    $body = "Dear Guardian, we have received a payment from {$student->first_name} for invoice ID {$studentCollection->invoice_id}, dated {$studentCollection->invoice_date}. Paid: {$studentCollection->total_paid}. ".get_option('school_name');

                    if ($this->notificationDispatch->shouldSendSms($channel)) {
                        $this->notificationDispatch->queueSms(
                            $mobile_number,
                            $body,
                            'Student',
                            (int) (Auth::id() ?: 0),
                            ['student_id' => $student->id]
                        );
                    }

                    if ($this->notificationDispatch->shouldSendWhatsApp($channel)) {
                        $this->notificationDispatch->queueWhatsApp(
                            $mobile_number,
                            'fee_collected',
                            [
                                'student_name' => trim($student->first_name.' '.$student->last_name),
                                'invoice_number' => $studentCollection->invoice_id,
                                'collection_date' => $studentCollection->invoice_date,
                                'amount_paid' => $studentCollection->total_paid,
                                'due_amount' => $studentCollection->total_due,
                                'school_name' => get_option('school_name'),
                                'contact' => get_option('phone'),
                            ],
                            $student->id,
                            'fee_collection',
                            $studentCollection->id
                        );
                    }
                } catch (Exception $e) {
                    return $this->responseError([], $e->getMessage());
                }
            }

            return $this->responseSuccess($studentCollection, 'Student collection has been created. Please download or print the PDF if needs.');
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function show(int $id): JsonResponse
    {
        $studentSession = $this->studentSessionService->findStudentSessionById($id);

        if (! $studentSession) {
            return $this->responseError([], 'Student not found.', 404);
        }

        $instituteId = get_institute_id();
        $branchId = get_branch_id();

        // Get active session
        $session = StudentSession::where([
            'institute_id' => $instituteId,
            'branch_id' => $branchId,
            'student_id' => $id,
        ])->first();

        // Load all fees with fee heads
        $amountConfig = Fee::with('feeHead')
            ->where([
                'institute_id' => $instituteId,
                'branch_id' => $branchId,
                'section_id' => $session->section_id,
                'class_id' => $session->class_id,
            ])->get();

        $total_fee = $amountConfig->sum('fee_amount');
        $total_fine = $amountConfig->sum('fine_amount');

        $cashLedgers = AccountingLedger::where([
            'institute_id' => $instituteId,
            'branch_id' => $branchId,
            'accounting_category_id' => 1,
        ])->get();

        // Extract unique feeHeads
        $feeHeads = $amountConfig->pluck('feeHead')->unique('id')->values();

        // Filter each feeHead's subheads by checking if collection already exists
        foreach ($feeHeads as $feeHead) {
            $filteredSubHeads = collect($feeHead->feeSubHeads ?? [])->filter(function ($subHead) use ($id, $instituteId, $branchId, $session, $feeHead) {
                // ✅ Total paid
                $totalPaid = StudentCollectionDetailsSubHead::where([
                    'student_id' => $id,
                    'institute_id' => $instituteId,
                    'branch_id' => $branchId,
                    'session_id' => $session->session_id,
                    'fee_head_id' => $feeHead->id,
                    'sub_head_id' => $subHead->id,
                ])->sum('paid_amount');

                $fee = Fee::where('fee_head_id', $feeHead->id)->first();
                $totalAmount = (float) $fee->fee_amount ?? 0;

                // ✅ If no config amount, still allow first-time display
                if ($totalAmount <= 0) {
                    return true;
                }

                $due = max(0, $totalAmount - $totalPaid);

                return $due > 0;
            })->values();

            // Overwrite with filtered subheads only
            $feeHead->setRelation('feeSubHeads', $filteredSubHeads);
        }

        return $this->responseSuccess([
            'studentSession' => $studentSession,
            'feeHeads' => $feeHeads,
            'total_fee' => $total_fee,
            'total_fine' => $total_fine,
            'cashLedgers' => $cashLedgers,
        ], 'Quick Collection Fetch Successfully.');
    }

    public function invoice(int $id): JsonResponse
    {
        $studentCollection = StudentCollection::where('id', $id)->where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())->first();

        return $this->responseSuccess($studentCollection, 'Student Collection Fetch Successfully.');
    }

    public function getCollectionAmounts(Request $request): JsonResponse
    {
        $studentId = (int) $request->student_id;
        $feeHeadData = $request->fee_head_id;
        $response = [];

        foreach ($feeHeadData as $feeHead) {
            $feeHeadId = $feeHead['id'];
            $feeSubHeadIds = $feeHead['fee_sub_head_ids'] ?? [];
            $response[] = [
                'fee_head_id' => $feeHeadId,
                'fee_sub_heads' => $feeSubHeadIds,
                'amounts' => $this->getCollectionAmountsByFeeHeadAndSubHeads(
                    $studentId,
                    $feeHeadId,
                    $feeSubHeadIds
                ),
            ];
        }

        return response()->json($response);
    }

    public function search(Request $request): JsonResponse
    {
        $studentSession = StudentSession::where('roll', $request->student_roll)->where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())->first();

        if (! $studentSession) {
            return $this->responseError([], 'Something went wrong. Entry Valid roll no.');
        }

        return $this->responseSuccess($studentSession, 'Student Session Fetch Successfully.');
    }

    public function getAttendanceFineAmount(int $id): JsonResponse
    {
        $student = $this->studentService->getStudentById($id);
        if (! $student) {
            return $this->responseError([], 'Student not found.', 404);
        }

        $attendance = DB::table('student_attendances')
            ->whereYear('date', current_year())
            ->where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->where('student_id', $id)
            ->where('attendance', 2)
            ->where('period_id', 1)
            ->count();

        $absentFine = AbsentFine::where('period_id', 1)
            ->where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())->first();

        $finePaidAmount = AttendanceFine::where('student_id', $id)
            ->where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->where('type', 'attendance_absent_fine')
            ->sum('fine_amount');

        // Use null coalescing operator to provide default value if null
        $attendanceWaiverAmount = 0;
        $attendanceWaiver = AttendanceWaiver::where('student_id', $id)
            ->where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->first();
        if ($attendanceWaiver) {
            $attendanceWaiverAmount = isset($attendanceWaiver->attendance_fine) ? floatval($attendanceWaiver->attendance_fine) : 0;
        }

        // Ensure absentFine is not null to avoid null property access
        $absentFineAmount = isset($absentFine->fee_amount) ? $absentFine->fee_amount : 0;

        // Calculate the total fine amount
        $totalFineAmount = $attendance * $absentFineAmount - ($finePaidAmount + $attendanceWaiverAmount);

        return response()->json([
            'message' => 'Attendance Fine Amount.',
            'attendance_fine' => $totalFineAmount,
        ], 200); // 200 is the default status code for success
    }

    public function getQuizFineAmount(int $id): JsonResponse
    {
        $quiz = DB::table('student_attendances')
            ->whereYear('date', current_year())
            ->where('student_id', $id)
            ->where('attendance', 2)
            ->where('period_id', 2)
            ->count();

        $absentFine = AbsentFine::where('period_id', 2)->where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())->first();
        $finePaidAmount = AttendanceFine::where('student_id', $id)
            ->where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->where('type', 'attendance_quiz_fine')
            ->sum('fine_amount');

        // Use null coalescing operator to provide default value if null
        $attendanceWaiver = AttendanceWaiver::where('student_id', $id)
            ->where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())->first();
        $quizWaiverAmount = isset($attendanceWaiver->quiz_fine) ? floatval($attendanceWaiver->quiz_fine) : 0;

        // Ensure absentFine is not null to avoid null property access
        $absentFineAmount = isset($absentFine->fee_amount) ? $absentFine->fee_amount : 0;

        $totalQuizFineAmount = $quiz * $absentFineAmount - ($finePaidAmount + $quizWaiverAmount);

        return response()->json([
            'message' => 'Quiz Fine Amount.',
            'quiz_fine' => $totalQuizFineAmount,
        ], 200); // 200 is the default status code for success
    }

    public function getLabFineAmount(int $id): JsonResponse
    {
        $lab = DB::table('student_attendances')
            ->where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->whereYear('date', current_year())
            ->where('student_id', $id)
            ->where('attendance', 2)
            ->where('period_id', 3)
            ->count();

        $absentFine = AbsentFine::where('period_id', 3)->where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())->first();
        $finePaidAmount = AttendanceFine::where('student_id', $id)->where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->where('type', 'attendance_lab_fine')
            ->sum('fine_amount');

        // Use null coalescing operator to provide default value if null
        $attendanceWaiver = AttendanceWaiver::where('student_id', $id)
            ->where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->first();
        $labWaiverAmount = isset($attendanceWaiver->lab_fine) ? floatval($attendanceWaiver->lab_fine) : 0;

        // Ensure absentFine is not null to avoid null property access
        $absentFineAmount = isset($absentFine->fee_amount) ? $absentFine->fee_amount : 0;
        $totalLabFineAmount = $lab * $absentFineAmount - ($finePaidAmount + $labWaiverAmount);

        return response()->json([
            'message' => 'Lab Fine Amount.',
            'lab_fine' => $totalLabFineAmount,
        ], 200); // 200 is the default status code for success
    }

    public function getTcAmount(): JsonResponse
    {
        $tcAmount = floatval(get_option('tc_amount'));

        return response()->json([
            'message' => 'TC Fee',
            'tc' => $tcAmount,
        ], 200);
    }

    public function getPaidReports(Request $request): JsonResponse
    {
        $sessionId = get_option('academic_year');
        $classId = $request->filled('class_id') ? (int) $request->class_id : null;
        $sectionId = $request->filled('section_id') ? (int) $request->section_id : null;
        $from_date = $request->from_date;
        $to_date = $request->to_date;

        $studentCollections = StudentCollection::query()
            ->where('student_collections.session_id', $sessionId)
            ->where('student_collections.institute_id', get_institute_id())
            ->where('student_collections.branch_id', get_branch_id())
            ->when($classId, fn ($query) => $query->where('student_collections.class_id', $classId))
            ->when($sectionId, function ($query) use ($sectionId, $sessionId) {
                $query->whereIn('student_collections.student_id', function ($subQuery) use ($sectionId, $sessionId) {
                    $subQuery->select('student_id')
                        ->from('student_sessions')
                        ->where('section_id', $sectionId)
                        ->where('session_id', $sessionId)
                        ->where('institute_id', get_institute_id())
                        ->where('branch_id', get_branch_id());
                });
            })
            ->when($from_date && $to_date, fn ($query) => $query->whereBetween('invoice_date', [$from_date, $to_date]))
            ->with([
                'student',
                'details' => function ($q) {
                    $q->with(['feeHead', 'subHeads']);
                },
            ])
            ->get();

        return $this->responseSuccess($studentCollections, 'Paid Reports Fetch.');
    }

    public function getUnpaidReports(Request $request): JsonResponse
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
        ]);

        $sessionId = (int) get_option('academic_year');
        $classId = (int) $request->class_id;
        $sectionId = (int) $request->section_id;
        $perPage = (int) ($request->per_page ?: 15);

        $students = Student::query()
            ->select('users.*', 'student_sessions.roll', 'classes.class_name', 'sections.section_name', 'students.id as id', 'student_groups.group_name', 'students.student_category_id', 'students.status as student_status')
            ->join('users', 'users.id', '=', 'students.user_id')
            ->join('student_sessions', 'students.id', '=', 'student_sessions.student_id')
            ->leftJoin('classes', 'classes.id', '=', 'student_sessions.class_id')
            ->leftJoin('sections', 'sections.id', '=', 'student_sessions.section_id')
            ->leftJoin('student_groups', 'student_sessions.group_id', '=', 'student_groups.id')
            ->where('student_sessions.session_id', $sessionId)
            ->where('student_sessions.institute_id', get_institute_id())
            ->where('student_sessions.branch_id', get_branch_id())
            ->where('users.user_type', 'Student')
            ->where('student_sessions.section_id', $sectionId)
            ->where('student_sessions.class_id', $classId)
            ->orderBy('student_sessions.roll', 'ASC')
            ->paginate($perPage);

        $studentData = [];
        foreach ($students as $student) {
            $id = $student->id;

            $session = StudentSession::query()
                ->where('student_id', $id)
                ->where('session_id', $sessionId)
                ->where('institute_id', get_institute_id())
                ->where('branch_id', get_branch_id())
                ->first();

            if (! $session) {
                continue;
            }

            // Fetch fee configurations for the student's class and section
            $amountConfig = Fee::where('section_id', $session->section_id)
                ->where('institute_id', get_institute_id())
                ->where('branch_id', get_branch_id())
                ->where('class_id', $session->class_id)
                ->with('feeHead.feeSubHeads')
                ->get();

            $feeHeads = [];
            $totalPaidAmount = 0;

            foreach ($amountConfig as $config) {
                $feeHead = $config->feeHead;

                $feeSubHeads = [];

                foreach ($feeHead->feeSubHeads as $feeSubHead) {
                    $studentCollectionDetailsSubHead = StudentCollectionDetailsSubHead::where('student_id', $id)
                        ->where('institute_id', get_institute_id())
                        ->where('branch_id', get_branch_id())
                        ->where('session_id', $session->session_id)
                        ->where('fee_head_id', $feeHead->id)
                        ->where('sub_head_id', $feeSubHead->id)
                        ->first();

                    if (! $studentCollectionDetailsSubHead || $studentCollectionDetailsSubHead->collectionDetail->total_paid === 0) {
                        $payableAmount = $this->getCollectionAmountsByFeeHeadAndSubHeads(
                            $id,
                            $feeHead->id,
                            (array) $feeSubHead->id
                        );

                        $feeSubHeads[] = [
                            'id' => $feeSubHead->id,
                            'name' => $feeSubHead->name,
                            'amount' => $payableAmount,
                        ];

                        $totalPaidAmount += $payableAmount['fee_and_fine_payable'];
                    }
                }

                $feeHeads[] = [
                    'id' => $feeHead->id,
                    'name' => $feeHead->name,
                    'feeSubHeads' => $feeSubHeads,
                ];
            }

            $studentData[] = [
                'id' => $student->id,
                'name' => $student->name,
                'roll' => $student->roll,
                'class_name' => $student->class_name,
                'section_name' => $student->section_name,
                'feeHeads' => $feeHeads,
                'total_paid_amount' => $totalPaidAmount,
            ];
        }

        $data = [
            'session_id' => $sessionId,
            'class_id' => $classId,
            'section_id' => $sectionId,
            'studentData' => $studentData,
        ];

        return $this->responseSuccess($data, 'Unpaid Reports.');
    }
}
