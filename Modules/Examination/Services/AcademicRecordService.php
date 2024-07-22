<?php

namespace Modules\Examination\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Academic\Models\Student;
use Modules\Academic\Models\StudentPromotion;
use Modules\Academic\Models\StudentSession;
use Modules\Examination\Models\ExamMark;
use Modules\Examination\Models\ResultPublication;

/**
 * A student's complete multi-session academic record: every session they were
 * enrolled in (class, section, roll), each term's result (subjects, total,
 * average, grade, position), the annual result where terms are configured,
 * and their promotion history. The basis for transcripts.
 *
 * Families (student / parent portals) only ever see PUBLISHED term results.
 */
class AcademicRecordService
{
    /** @var array<string, Collection> ranked class results per class|exam|section|session */
    private array $classCache = [];

    public function __construct(
        private readonly ResultCalculationService $calculation,
        private readonly GrandFinalResultService $grandFinal,
    ) {}

    public function forStudent(Student $student, bool $publishedOnly): array
    {
        $sessions = StudentSession::with(['session', 'class:id,class_name', 'section:id,section_name', 'group:id,group_name'])
            ->where('student_id', $student->id)
            ->orderBy('session_id')
            ->get();

        $published = ResultPublication::where('status', ResultPublication::STATUS_PUBLISHED)
            ->get(['exam_id', 'class_id'])
            ->map(fn ($p) => $p->exam_id.'|'.$p->class_id)
            ->flip();

        $records = $sessions->map(function (StudentSession $session) use ($publishedOnly, $published) {
            $examIds = ExamMark::where('student_id', $session->student_id)
                ->where('session_id', $session->session_id)
                ->where('class_id', $session->class_id)
                ->distinct()
                ->pluck('exam_id');

            $exams = DB::table('exams')->whereIn('id', $examIds)->orderBy('id')->get(['id', 'name']);

            $terms = $exams
                ->filter(fn ($exam) => ! $publishedOnly || $published->has($exam->id.'|'.$session->class_id))
                ->map(fn ($exam) => $this->termSummary($session, (int) $exam->id, $exam->name))
                ->values();

            $annual = $this->grandFinal->calculateForStudentSession($session);

            return [
                'student_session_id' => $session->id,
                'session_id' => $session->session_id,
                'session_name' => $session->session?->session,
                'class_id' => $session->class_id,
                'class_name' => $session->class?->class_name,
                'section_name' => $session->section?->section_name,
                'group_name' => $session->group?->group_name,
                'roll' => $session->roll,
                'terms' => $terms->all(),
                'annual' => ($annual['configured'] ?? false) && (! $publishedOnly || $terms->isNotEmpty()) ? [
                    'calculation_method' => $annual['calculation_method'],
                    'final_score' => $annual['final_score'],
                    'final_obtained' => $annual['final_obtained'],
                    'final_full_marks' => $annual['final_full_marks'],
                    'grade' => $annual['grade_name'],
                    'grade_point' => (float) $annual['grade_point'],
                ] : null,
            ];
        })->values();

        $promotions = StudentPromotion::with('batch:id,from_session_id,to_session_id,status,created_at')
            ->where('student_id', $student->id)
            ->whereHas('batch', fn ($q) => $q->where('status', 'completed'))
            ->orderBy('id')
            ->get()
            ->map(fn (StudentPromotion $p) => [
                'action' => $p->action,
                'from_session_id' => $p->batch?->from_session_id,
                'to_session_id' => $p->batch?->to_session_id,
                'from_class_id' => $p->from_class_id,
                'to_class_id' => $p->to_class_id,
                'date' => $p->created_at?->toDateString(),
            ]);

        $allAverages = $records->flatMap(fn ($r) => collect($r['terms'])->pluck('average'))->filter(fn ($v) => $v !== null);

        return [
            'student' => [
                'id' => $student->id,
                'name' => trim($student->first_name.' '.$student->last_name),
                'admission_no' => $student->admission_no ?? null,
                'registration_no' => $student->registration_no,
                'birthday' => $student->birthday,
                'gender' => $student->gender,
                'date_of_admission' => $student->date_of_admission,
                'graduated_at' => $student->graduated_at,
            ],
            'sessions' => $records->all(),
            'promotions' => $promotions->all(),
            'overall_average' => $allAverages->isNotEmpty() ? round($allAverages->avg(), 2) : null,
        ];
    }

    private function termSummary(StudentSession $session, int $examId, string $examName): array
    {
        $key = implode('|', [$session->class_id, $examId, $session->section_id, $session->session_id]);
        $rows = $this->classCache[$key] ??= $this->calculation->buildClassTermResults(
            (int) $session->class_id,
            $examId,
            $session->section_id ? (int) $session->section_id : null,
            null,
            null,
            (int) $session->session_id,
        );

        $row = $rows->firstWhere('student_id', $session->student_id)
            ?? $this->calculation->buildStudentTermResult($session, $examId);

        return [
            'exam_id' => $examId,
            'exam_name' => $examName,
            'total_marks' => $row['total_marks'],
            'total_full_marks' => $row['total_full_marks'],
            'average' => $row['percentage'],
            'gpa' => $row['gpa'],
            'grade' => $row['grade'],
            'status' => $row['status'],
            'position' => $row['position'] ?? null,
            'students_in_class' => $rows->count(),
            'subjects' => collect($row['subjects'])->map(fn ($s) => [
                'subject_id' => $s['subject_id'],
                'subject_name' => $s['subject_name'],
                'full_marks' => $s['full_marks'],
                'obtained' => $s['obtained'],
                'grade' => $s['grade'],
                'grade_point' => $s['grade_point'],
                'status' => $s['status'],
            ])->values()->all(),
        ];
    }
}
