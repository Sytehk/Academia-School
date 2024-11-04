<?php

namespace Modules\ParentModule\Services;

use App\Traits\StudentCollectionTrait;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
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
use Modules\Academic\Models\StudentAttendance;
use Modules\Academic\Models\StudentSession;
use Modules\Academic\Models\Subject;
use Modules\Examination\Models\ClassExam;
use Modules\Examination\Models\ExamMark;
use Modules\Finance\Models\Fee;
use Modules\Finance\Models\StudentCollectionDetailsSubHead;
use Modules\Library\Models\BookIssue;
use Modules\ParentModule\Models\ParentModel;
use Modules\Teacher\Models\Behavior;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * All read logic for the Parent panel.
 *
 * Every method operates on the currently *active child* (resolved through
 * get_authenticated_parent_student_id(), which is itself ownership-gated), so the
 * panel can never surface another family's data. The API controller was used as the
 * behavioural reference; this service is the canonical, view-agnostic home for that
 * logic and centralises the child-ownership boundary in one place.
 */
class ParentService
{
    use StudentCollectionTrait;

    /**
     * The logged-in parent's ParentModel row.
     */
    public function parent(): ?ParentModel
    {
        return ParentModel::where('user_id', auth()->id())
            ->with('user')
            ->first();
    }

    /**
     * Children of the logged-in parent (decorated with class/section).
     */
    public function children(): Collection
    {
        return get_parent_children();
    }

    /**
     * The active child's student id, or null when the parent has no children.
     */
    public function activeChildId(): ?int
    {
        $id = get_authenticated_parent_student_id();

        return $id ? (int) $id : null;
    }

    /**
     * The active child's session row, eager-loaded for display.
     */
    public function activeChildSession(): ?StudentSession
    {
        $studentId = $this->activeChildId();
        if (! $studentId) {
            return null;
        }

        return StudentSession::with(['student.user', 'class', 'section', 'group'])
            ->where('student_id', $studentId)
            ->where('session_id', get_option('academic_year'))
            ->first()
            ?? StudentSession::with(['student.user', 'class', 'section', 'group'])
                ->where('student_id', $studentId)
                ->latest('id')
                ->first();
    }

    /**
     * Make $studentId the active child, but only if it truly belongs to this parent.
     * Persists both the session selection and the stored default so the choice sticks
     * across logins.
     *
     * @throws HttpException 403 when the student is not the parent's child.
     */
    public function setActiveChild(int $studentId): void
    {
        if (! parent_owns_child($studentId)) {
            abort(403, _lang('This student is not linked to your account.'));
        }

        session(['parent_active_child_id' => $studentId]);

        ParentModel::where('user_id', auth()->id())->update(['student_id' => $studentId]);
    }

    /**
     * Aggregated dashboard payload for the active child.
     */
    public function dashboard(): array
    {
        $studentId = $this->activeChildId();
        $session = $this->activeChildSession();

        if (! $studentId || ! $session) {
            return [
                'has_child' => false,
                'total_children' => $this->children()->count(),
            ];
        }

        $classId = $session->class_id;
        $today = now()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        // This-month attendance for the active child (single query, aggregated in PHP).
        $monthly = StudentAttendance::where('student_id', $studentId)
            ->where('period_id', 1)
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->get(['attendance', 'date']);

        $present = $monthly->where('attendance', '1')->count();
        $absent = $monthly->where('attendance', '2')->count();
        $late = $monthly->where('attendance', '3')->count();
        $monthlyTotal = $monthly->count();

        $todayStatus = optional($monthly->firstWhere('date', $today))->attendance;

        // Fees — reuse the same computation the fee pages use so figures always agree.
        $unpaid = $this->unpaidFees();
        $pendingFees = $unpaid['total_due_raw'] ?? 0;
        $paidFees = (float) DB::table('student_collections')
            ->where('student_id', $studentId)
            ->sum('total_paid');

        $upcomingExams = ExamSchedule::where('class_id', $classId)
            ->whereDate('date', '>=', $today)
            ->count();

        $homeworkCount = Assignment::where('class_id', $classId)
            ->where('section_id', $session->section_id)
            ->where('session_id', get_option('academic_year'))
            ->when(function_exists('get_institute_id'), fn ($q) => $q->where('institute_id', get_institute_id()))
            ->count();

        return [
            'has_child' => true,
            'total_children' => $this->children()->count(),
            'child' => $session,
            'attendance' => [
                'today_status' => $this->attendanceLabel($todayStatus),
                'present' => $present,
                'absent' => $absent,
                'late' => $late,
                'total' => $monthlyTotal,
                'percentage' => $monthlyTotal > 0 ? round(($present / $monthlyTotal) * 100, 1) : 0,
            ],
            'fees' => [
                'pending' => $pendingFees,
                'paid' => $paidFees,
            ],
            'upcoming_exams' => $upcomingExams,
            'homework' => $homeworkCount,
            'notices' => Notice::select('id', 'title', 'notice', 'date', 'created_by')
                ->latest('date')
                ->limit(5)
                ->get(),
            'events' => Event::select('id', 'start_date', 'end_date', 'name', 'details', 'location')
                ->whereDate('end_date', '>=', $today)
                ->orderBy('start_date')
                ->limit(5)
                ->get(),
        ];
    }

    /**
     * Month grid of attendance for the active child.
     */
    public function attendance(int $month, int $year): array
    {
        $studentId = $this->activeChildId();
        $numDays = (int) cal_days_in_month(CAL_GREGORIAN, $month, $year);

        $records = StudentAttendance::where('student_id', $studentId)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get(['date', 'attendance', 'check_in_at', 'check_out_at'])
            ->keyBy(fn ($row) => Carbon::parse($row->date)->toDateString());

        $days = [];
        for ($day = 1; $day <= $numDays; $day++) {
            $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $record = $records->get($date);
            $days[] = [
                'date' => $date,
                'day' => date('l', strtotime($date)),
                'status' => $this->attendanceLabel(optional($record)->attendance),
                // Null for manual/historical records and for any day with no
                // attendance row at all — never backfilled or guessed.
                'check_in_at' => $record?->check_in_at?->toDateTimeString(),
                'check_out_at' => $record?->check_out_at?->toDateTimeString(),
            ];
        }

        return [
            'month' => $month,
            'year' => $year,
            'days' => $days,
            'summary' => [
                'present' => $records->where('attendance', '1')->count(),
                'absent' => $records->where('attendance', '2')->count(),
                'late' => $records->where('attendance', '3')->count(),
            ],
        ];
    }

    /**
     * Class routine grid for the active child.
     */
    public function classRoutine(): array
    {
        $session = $this->activeChildSession();
        if (! $session) {
            return ['class' => null, 'section' => null, 'routine' => []];
        }

        return [
            'class' => ClassModel::find($session->class_id),
            'section' => Section::find($session->section_id),
            'routine' => ClassRoutine::getRoutineView($session->class_id, $session->section_id),
        ];
    }

    /**
     * Exam routine (schedules grouped per exam) for the active child.
     */
    public function examRoutine(): array
    {
        $session = $this->activeChildSession();
        if (! $session || ! $session->student) {
            return [];
        }

        $classId = $session->class_id;
        $groupId = $session->group_id;

        $subjects = $this->subjectsForClass($classId, $groupId);
        $subjectIds = $subjects->pluck('id');

        $schedules = ExamSchedule::whereIn('subject_id', $subjectIds)
            ->get()
            ->groupBy('exam_id');

        $subjectsById = $subjects->keyBy('id');

        return Exam::all()->map(function ($exam) use ($schedules, $subjectsById) {
            $rows = ($schedules[$exam->id] ?? collect())->map(function ($schedule) use ($subjectsById) {
                $subject = $subjectsById->get($schedule->subject_id);

                return [
                    'subject_name' => $subject->subject_name ?? '',
                    'subject_code' => $subject->subject_code ?? '',
                    'date' => $schedule->date,
                    'start_time' => $schedule->start_time,
                    'end_time' => $schedule->end_time,
                    'room' => $schedule->room,
                ];
            })->values();

            return ['exam' => $exam, 'subjects' => $rows];
        })->filter(fn ($group) => $group['subjects']->isNotEmpty())->values()->all();
    }

    /**
     * Subjects (compulsory + group) for the active child.
     */
    public function subjects(): Collection
    {
        $session = $this->activeChildSession();
        if (! $session) {
            return collect();
        }

        return $this->subjectsForClass($session->class_id, $session->group_id);
    }

    /**
     * Paginated assignments for the active child's class/section.
     */
    public function assignments(int $perPage = 10)
    {
        $session = $this->activeChildSession();
        if (! $session) {
            return Assignment::whereRaw('1 = 0')->paginate($perPage);
        }

        return Assignment::with('subject:id,subject_name,subject_code')
            ->where('class_id', $session->class_id)
            ->where('section_id', $session->section_id)
            ->where('session_id', get_option('academic_year'))
            ->where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->latest('id')
            ->paginate($perPage);
    }

    /**
     * Exams available for the active child's class.
     */
    public function examList(): Collection
    {
        $session = $this->activeChildSession();
        if (! $session) {
            return collect();
        }

        return ClassExam::with(['exam', 'class', 'meritType'])
            ->where('class_id', $session->class_id)
            ->get();
    }

    /**
     * Full mark sheet / result for the active child in a given exam.
     */
    public function examResult(int $examId): array
    {
        $studentId = $this->activeChildId();
        $session = $this->activeChildSession();

        if (! $session) {
            return ['found' => false];
        }

        $examMarks = ExamMark::with(['subject:id,subject_name', 'exam:id,name'])
            ->where('student_id', $studentId)
            ->where('exam_id', $examId)
            ->get();

        if ($examMarks->isEmpty()) {
            return ['found' => false, 'session' => $session];
        }

        $totalObtained = (float) $examMarks->sum('total_marks');
        $totalGradePoint = (float) $examMarks->sum('grade_point');
        $totalSubjects = $examMarks->count();
        $fullPerSubject = 100;
        $totalFull = $fullPerSubject * $totalSubjects;
        $percentage = $totalFull > 0 ? round(($totalObtained / $totalFull) * 100, 2) : 0;

        $subjects = $examMarks->map(fn ($mark) => [
            'name' => $mark->subject->subject_name ?? '',
            'full_marks' => $fullPerSubject,
            'marks_obtained' => (float) $mark->total_marks,
            'grade' => $mark->grade,
            'remarks' => $this->remarkForGrade($mark->grade),
        ]);

        return [
            'found' => true,
            'session' => $session,
            'exam' => [
                'name' => $examMarks->first()->exam->name ?? '',
                'total_marks' => $totalFull,
                'obtained_marks' => $totalObtained,
                'percentage' => $percentage,
                'grade' => $this->overallGrade($percentage),
                'grade_point' => $totalSubjects > 0 ? round($totalGradePoint / $totalSubjects, 2) : 0,
                'result' => $percentage >= 40 ? _lang('Pass') : _lang('Fail'),
            ],
            'subjects' => $subjects,
            'position' => $this->classPosition($session->class_id, $session->section_id, $examId, $studentId),
        ];
    }

    /**
     * Unpaid fee breakdown for the active child.
     */
    public function unpaidFees(): array
    {
        $studentId = $this->activeChildId();
        $session = $this->activeChildSession();

        if (! $session || ! $session->student) {
            return ['fee_heads' => [], 'total_due' => '0.00', 'total_due_raw' => 0, 'student' => null];
        }

        $amountConfigs = Fee::with('feeHead.feeSubHeads')
            ->where('class_id', $session->class_id)
            ->where('section_id', $session->section_id)
            ->get();

        $feeHeadsData = [];
        $totalDue = 0.0;

        foreach ($amountConfigs as $config) {
            $feeHead = $config->feeHead;
            if (! $feeHead) {
                continue;
            }

            foreach ($feeHead->feeSubHeads as $subHead) {
                $collectionSub = StudentCollectionDetailsSubHead::where('student_id', $studentId)
                    ->where('session_id', $session->session_id)
                    ->where('fee_head_id', $feeHead->id)
                    ->where('sub_head_id', $subHead->id)
                    ->first();

                if (! $collectionSub || optional($collectionSub->collectionDetail)->total_paid == 0) {
                    $payable = $this->getCollectionAmountsByFeeHeadAndSubHeads($studentId, $feeHead->id, [$subHead->id]);

                    if (! isset($feeHeadsData[$feeHead->id])) {
                        $feeHeadsData[$feeHead->id] = [
                            'fee_head_id' => $feeHead->id,
                            'fee_head_name' => $feeHead->name,
                            'sub_heads' => [],
                        ];
                    }

                    $feeHeadsData[$feeHead->id]['sub_heads'][] = [
                        'sub_head_id' => $subHead->id,
                        'sub_head_name' => $subHead->name,
                        'payable' => (float) $payable['fee_and_fine_payable'],
                    ];

                    $totalDue += (float) $payable['fee_and_fine_payable'];
                }
            }
        }

        return [
            'student' => $session,
            'fee_heads' => array_values($feeHeadsData),
            'total_due' => number_format($totalDue, 2),
            'total_due_raw' => $totalDue,
        ];
    }

    /**
     * Payment / collection history for the active child.
     */
    public function paymentHistory(): array
    {
        $studentId = $this->activeChildId();

        $totalPaid = (float) DB::table('student_collections')
            ->where('student_id', $studentId)
            ->sum('total_paid');

        $history = DB::table('student_collection_details as scd')
            ->join('student_collections as sc', 'sc.id', '=', 'scd.student_collection_id')
            ->leftJoin('fee_heads as fh', 'fh.id', '=', 'scd.fee_head_id')
            ->where('sc.student_id', $studentId)
            ->orderByDesc('sc.invoice_date')
            ->select(
                'sc.invoice_id',
                'sc.invoice_date',
                'fh.name as fee_head_name',
                'scd.fee_and_fine_paid',
                'scd.waiver',
                'scd.fine_payable',
                'scd.previous_due_paid'
            )
            ->get();

        return [
            'total_paid' => number_format($totalPaid, 2),
            'history' => $history,
        ];
    }

    public function notices(int $perPage = 10)
    {
        return Notice::select('id', 'title', 'notice', 'date', 'created_by')
            ->with('userType')
            ->latest('date')
            ->paginate($perPage);
    }

    public function events(int $perPage = 10)
    {
        return Event::select('id', 'start_date', 'end_date', 'name', 'details', 'location')
            ->orderByDesc('start_date')
            ->paginate($perPage);
    }

    public function behaviors(int $perPage = 10)
    {
        return Behavior::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->where('student_id', $this->activeChildId())
            ->latest('id')
            ->paginate($perPage);
    }

    /**
     * Library membership and issue history for the active child.
     */
    public function libraryHistory(): array
    {
        $studentId = $this->activeChildId();

        $member = LibraryMember::join('users', 'users.id', '=', 'library_members.user_id')
            ->where('library_members.student_id', $studentId)
            ->select('users.name', 'users.email', 'users.phone', 'users.image', 'library_members.*')
            ->first();

        if (! $member) {
            return ['member' => null, 'issues' => collect()];
        }

        $issues = BookIssue::join('books', 'books.id', '=', 'book_issues.book_id')
            ->where('book_issues.student_id', $studentId)
            ->orderByDesc('book_issues.id')
            ->select(
                'book_issues.id',
                'book_issues.issue_date',
                'book_issues.due_date',
                'book_issues.return_date',
                'book_issues.type',
                'books.book_name',
                'books.code',
                'books.category'
            )
            ->get();

        return ['member' => $member, 'issues' => $issues];
    }

    /**
     * Attendance-fine report for the active child (current year).
     */
    public function attendanceFineReport(): array
    {
        $studentId = $this->activeChildId();
        $session = $this->activeChildSession();

        if (! $session) {
            return ['months' => [], 'total_fine' => '0.00'];
        }

        $fine = AbsentFine::where('period_id', 1)->first();
        $fineAmount = $fine ? (float) $fine->fee_amount : 0.0;

        $rows = DB::table('student_attendances')
            ->where('student_id', $studentId)
            ->where('class_id', $session->class_id)
            ->where('section_id', $session->section_id)
            ->where('attendance', 2)
            ->where('period_id', 1)
            ->whereYear('date', current_year())
            ->select(DB::raw('MONTH(date) as month'), DB::raw('COUNT(id) as absent_count'))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $months = [];
        $total = 0.0;
        foreach ($rows as $row) {
            $amount = $row->absent_count * $fineAmount;
            $months[] = [
                'month_name' => Carbon::create()->month((int) $row->month)->format('F'),
                'absent_count' => (int) $row->absent_count,
                'fine_amount' => number_format($amount, 2),
            ];
            $total += $amount;
        }

        return [
            'fine_per_day' => number_format($fineAmount, 2),
            'months' => $months,
            'total_fine' => number_format($total, 2),
        ];
    }

    // ---------------------------------------------------------------------
    // Internal helpers
    // ---------------------------------------------------------------------

    /**
     * Compulsory (group-less) + group-specific subjects for a class.
     */
    private function subjectsForClass(int $classId, $groupId): Collection
    {
        $compulsory = Subject::select('id', 'subject_name', 'subject_code', 'subject_type', 'class_id', 'group_id')
            ->where('class_id', $classId)
            ->whereNull('group_id')
            ->get();

        $group = collect();
        if ($groupId) {
            $group = Subject::select('id', 'subject_name', 'subject_code', 'subject_type', 'class_id', 'group_id')
                ->where('class_id', $classId)
                ->where('group_id', $groupId)
                ->get();
        }

        return $compulsory->merge($group);
    }

    private function attendanceLabel($value): string
    {
        return [
            '1' => _lang('Present'),
            '2' => _lang('Absent'),
            '3' => _lang('Late'),
        ][$value] ?? '';
    }

    private function remarkForGrade(?string $grade): string
    {
        return match ($grade) {
            'A+' => _lang('Excellent'),
            'A' => _lang('Very Good'),
            'B+' => _lang('Good'),
            'B' => _lang('Satisfactory'),
            'C' => _lang('Needs Improvement'),
            default => _lang('Poor'),
        };
    }

    private function overallGrade(float $percentage): string
    {
        return match (true) {
            $percentage >= 80 => 'A+',
            $percentage >= 70 => 'A',
            $percentage >= 60 => 'B+',
            $percentage >= 50 => 'B',
            $percentage >= 40 => 'C',
            default => 'F',
        };
    }

    private function classPosition(int $classId, int $sectionId, int $examId, int $studentId): string
    {
        $ranked = ExamMark::selectRaw('student_id, SUM(total_marks) as total')
            ->where('exam_id', $examId)
            ->where('class_id', $classId)
            ->where('group_id', $sectionId)
            ->groupBy('student_id')
            ->orderByDesc('total')
            ->get();

        foreach ($ranked as $index => $mark) {
            if ((int) $mark->student_id === $studentId) {
                return (string) ($index + 1);
            }
        }

        return 'N/A';
    }
}
