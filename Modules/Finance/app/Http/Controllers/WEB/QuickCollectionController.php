<?php

namespace Modules\Finance\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use App\Services\NotificationDispatchService;
use App\Traits\StudentCollectionTrait;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Modules\Academic\Models\AbsentFine;
use Modules\Academic\Models\Student;
use Modules\Academic\Models\StudentSession;
use Modules\Academic\Repositories\SectionRepository;
use Modules\Academic\Services\StudentSessionService;
use Modules\Accounting\Models\AccountingLedger;
use Modules\Finance\Http\Requests\StudentCollectionCreateRequest;
use Modules\Finance\Models\AttendanceFine;
use Modules\Finance\Models\AttendanceWaiver;
use Modules\Finance\Models\Fee;
use Modules\Finance\Models\StudentCollection;
use Modules\Finance\Models\StudentCollectionDetailsSubHead;
use RealRashid\SweetAlert\Facades\Alert;

class QuickCollectionController extends Controller
{
    use StudentCollectionTrait;

    public function __construct(
        private readonly StudentSessionService $studentSessionService,
        private readonly SectionRepository $sectionRepo,
        private readonly NotificationDispatchService $notificationDispatch
    ) {}

    private function queueFeeCollectionNotifications(Student $student, StudentCollection $studentCollection, string $channel): void
    {
        $mobileNumber = $this->notificationDispatch->studentPhone($student);
        if (! $mobileNumber) {
            return;
        }

        $paymentMethod = $studentCollection->receive_ledger_id
            ? AccountingLedger::find($studentCollection->receive_ledger_id)?->name
            : null;

        $parameters = [
            'student_name' => trim($student->first_name.' '.$student->last_name),
            'invoice_number' => $studentCollection->invoice_id,
            'collection_date' => $studentCollection->invoice_date,
            'amount_paid' => $studentCollection->total_paid,
            'due_amount' => $studentCollection->total_due,
            'payment_method' => $paymentMethod,
            'receipt_number' => $studentCollection->invoice_id,
            'school_name' => get_option('school_name'),
            'contact' => get_option('phone'),
            'receipt_link' => url('student-collection/invoice/'.$studentCollection->id),
        ];

        if ($this->notificationDispatch->shouldSendSms($channel)) {
            $this->notificationDispatch->queueSms(
                $mobileNumber,
                "Dear Guardian, we have received a payment from {$parameters['student_name']} for invoice ID {$parameters['invoice_number']}, dated {$parameters['collection_date']}. Paid: {$parameters['amount_paid']}. Due: {$parameters['due_amount']}. {$parameters['school_name']}",
                'Student',
                (int) (auth()->id() ?: 0),
                ['student_id' => $student->id]
            );
        }

        if ($this->notificationDispatch->shouldSendWhatsApp($channel)) {
            $this->notificationDispatch->queueWhatsApp(
                $mobileNumber,
                'fee_collected',
                $parameters,
                $student->id,
                'fee_collection',
                $studentCollection->id
            );
        }
    }

    public function index(): View|Factory
    {
        $sectionId = (int) request()->section_id;
        $classId = (int) request()->class_id;
        $sectionsData = $this->sectionRepo->getSectionsByClassId($classId);

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
            ->get();

        return view('finance::quick_collection.index', [
            'students' => $students,
            'sectionId' => $sectionId,
            'classId' => $classId,
            'sectionsData' => $sectionsData,
        ]);
    }

    public function store(StudentCollectionCreateRequest $request): Redirector|RedirectResponse
    {
        try {
            $studentCollection = $this->createCollection(
                $request->all()
            );

            $student = Student::where('id', $studentCollection->student_id)->first();
            $channel = $this->notificationDispatch->resolveChannel($request->notify_channel, $request->sms_status);
            if ($student && $channel !== NotificationDispatchService::CHANNEL_NONE) {
                $this->queueFeeCollectionNotifications($student, $studentCollection, $channel);
            }

            Alert::toast('Student collection has been created. Please download or print the PDF if needs.', 'success');

            return redirect('student-collection/invoice/'.$studentCollection->id);
        } catch (Exception $e) {
            Alert::error('Error', $e->getMessage());

            return back();
        }
    }

    public function show(int $id): Factory|View
    {
        $studentSession = $this->studentSessionService->findStudentSessionById($id);
        if (! $studentSession) {
            $sectionId = (int) request()->section_id;
            $classId = (int) request()->class_id;
            $sectionsData = $this->sectionRepo->getSectionsByClassId($classId);

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
                ->get();

            return view('finance::quick_collection.index', [
                'students' => $students,
                'sectionId' => $sectionId,
                'classId' => $classId,
                'sectionsData' => $sectionsData,
            ]);
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

        return view('finance::quick_collection.show', [
            'studentSession' => $this->studentSessionService->findStudentSessionById($id),
            'feeHeads' => $feeHeads,
            'total_fee' => $total_fee,
            'total_fine' => $total_fine,
            'cashLedgers' => $cashLedgers,
        ]);
    }

    public function invoice($id): Factory|View
    {
        $studentCollection = StudentCollection::where('id', $id)->first();

        return view('finance::quick_collection.invoice-pdf', compact('studentCollection'));
    }

    public function getCollectionAmounts(Request $request)
    {
        $studentId = $request->input('student_id');
        $feeHeadId = $request->input('fee_head_id');
        $feeSubHeadIds = (array) $request->input('fee_sub_head_ids');

        return response()->json(
            $this->getCollectionAmountsByFeeHeadAndSubHeads(
                $studentId,
                $feeHeadId,
                $feeSubHeadIds
            )
        );
    }

    public function search(Request $request): RedirectResponse
    {
        $studentSession = StudentSession::where('roll', $request->student_roll)
            ->where('class_id', (int) $request->class_id)
            ->where('section_id', (int) $request->section_id)
            ->first();

        if (! $studentSession) {
            Alert::error('Error', _lang('No student found with this class, section and roll.'));

            return back();
        }

        Alert::toast('Student Search Found.', 'success');

        return redirect('quick-collection/'.$studentSession->student_id);
    }

    public function getAttendanceFineAmount(Request $request, int $id): float
    {
        $attendance = DB::table('student_attendances')
            ->whereYear('date', current_year())
            ->where('student_id', $id)
            ->where('attendance', 2)
            ->where('period_id', 1)
            ->count();

        $absentFine = AbsentFine::where('period_id', 1)->first();
        $finePaidAmount = AttendanceFine::where('student_id', $id)
            ->where('type', 'attendance_absent_fine')
            ->sum('fine_amount');

        // Use null coalescing operator to provide default value if null
        $attendanceWaiver1 = AttendanceWaiver::where('student_id', $id)->first();
        $attendanceWaiverAmount1 = isset($attendanceWaiver1->attendance_fine) ? floatval($attendanceWaiver1->attendance_fine) : 0;

        // Ensure absentFine is not null to avoid null property access
        $absentFineAmount = isset($absentFine->fee_amount) ? $absentFine->fee_amount : 0;

        return $attendance * $absentFineAmount - ($finePaidAmount + $attendanceWaiverAmount1);
    }

    public function getQuizFineAmount(Request $request, int $id): float
    {
        $quiz = DB::table('student_attendances')
            ->whereYear('date', current_year())
            ->where('student_id', $id)
            ->where('attendance', 2)
            ->where('period_id', 6)
            ->count();

        $absentFine = AbsentFine::where('period_id', 6)->first();
        $finePaidAmount = AttendanceFine::where('student_id', $id)
            ->where('type', 'attendance_quiz_fine')
            ->sum('fine_amount');

        // Use null coalescing operator to provide default value if null
        $attendanceWaiver2 = AttendanceWaiver::where('student_id', $id)->first();
        $quizWaiverAmount2 = isset($attendanceWaiver2->quiz_fine) ? floatval($attendanceWaiver2->quiz_fine) : 0;

        // Ensure absentFine is not null to avoid null property access
        $absentFineAmount = isset($absentFine->fee_amount) ? $absentFine->fee_amount : 0;

        return $quiz * $absentFineAmount - ($finePaidAmount + $quizWaiverAmount2);
    }

    public function getLabFineAmount(Request $request, int $id): float
    {
        $lab = DB::table('student_attendances')
            ->whereYear('date', current_year())
            ->where('student_id', $id)
            ->where('attendance', 2)
            ->where('period_id', 7)
            ->count();

        $absentFine3 = AbsentFine::where('period_id', 7)->first();
        $finePaidAmount2 = AttendanceFine::where('student_id', $id)
            ->where('type', 'attendance_lab_fine')
            ->sum('fine_amount');

        // Use null coalescing operator to provide default value if null
        $attendanceWaiver = AttendanceWaiver::where('student_id', $id)->first();
        $labWaiverAmount = isset($attendanceWaiver->lab_fine) ? floatval($attendanceWaiver->lab_fine) : 0;

        // Ensure absentFine3 is not null to avoid null property access
        $absentFineAmount = isset($absentFine3->fee_amount) ? $absentFine3->fee_amount : 0;

        return $lab * $absentFineAmount - ($finePaidAmount2 + $labWaiverAmount);
    }

    public function getTcAmount(Request $request): float
    {
        return floatval(get_option('tc_amount'));
    }
}
