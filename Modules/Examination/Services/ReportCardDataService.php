<?php

namespace Modules\Examination\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Academic\Models\StudentSession;
use Modules\Examination\Models\ExamMark;
use Modules\Examination\Models\ExamTermDetail;
use Modules\Examination\Models\GrandFinalClassExam;
use Modules\Examination\Models\RemarkConfig;
use Modules\Examination\Models\StudentResultComment;

/**
 * The parts of a (Nigerian-style) report card that the base result engine did
 * not produce: subject positions and class statistics, term attendance from
 * daily attendance, a cumulative multi-term view, written comments and
 * next-term information.
 */
class ReportCardDataService
{
    /** student_attendances.attendance values counted as present (1 present, 3 late). */
    private const PRESENT = [1, 3];

    /**
     * Per-subject class statistics and every student's subject positions,
     * computed from ranked class results (ResultCalculationService rows).
     *
     * @return array{subjects: array<int, array{highest: ?float, lowest: ?float, average: ?float, sat: int}>,
     *               positions: array<int, array<int, int>>, class: array<string, mixed>}
     */
    public function classStatistics(Collection $classRows): array
    {
        $bySubject = [];
        foreach ($classRows as $row) {
            foreach ($row['subjects'] ?? [] as $subject) {
                if ($subject['obtained'] === null || ($subject['is_exempted'] ?? false)) {
                    continue;
                }
                $bySubject[$subject['subject_id']][$row['student_id']] = (float) $subject['obtained'];
            }
        }

        $subjects = [];
        $positions = [];
        foreach ($bySubject as $subjectId => $scores) {
            $subjects[$subjectId] = [
                'highest' => round(max($scores), 2),
                'lowest' => round(min($scores), 2),
                'average' => round(array_sum($scores) / count($scores), 2),
                'sat' => count($scores),
            ];

            // Competition ranking: ties share a position, the next rank skips.
            arsort($scores);
            $rank = 0;
            $index = 0;
            $previous = null;
            foreach ($scores as $studentId => $score) {
                $index++;
                if ($score !== $previous) {
                    $rank = $index;
                    $previous = $score;
                }
                $positions[$studentId][$subjectId] = $rank;
            }
        }

        $counted = $classRows->filter(fn ($row) => ($row['status'] ?? '') !== 'not_published');
        $averages = $counted->map(fn ($row) => (float) ($row['percentage'] ?? 0));

        return [
            'subjects' => $subjects,
            'positions' => $positions,
            'class' => [
                'students_in_class' => $classRows->count(),
                'students_with_results' => $counted->count(),
                'class_average' => $averages->isNotEmpty() ? round($averages->avg(), 2) : null,
                'highest_average' => $averages->isNotEmpty() ? round($averages->max(), 2) : null,
                'lowest_average' => $averages->isNotEmpty() ? round($averages->min(), 2) : null,
            ],
        ];
    }

    public function termDetails(int $examId): ?ExamTermDetail
    {
        return ExamTermDetail::where('exam_id', $examId)->first();
    }

    /**
     * Days present / absent for the term, from daily student attendance
     * (one row per period is possible, so days are counted distinctly).
     */
    public function termAttendance(StudentSession $session, ?ExamTermDetail $term): ?array
    {
        if (! $term || ! $term->term_start_date || ! $term->term_end_date) {
            return null;
        }

        $range = [$term->term_start_date->toDateString(), $term->term_end_date->toDateString()];

        $mine = DB::table('student_attendances')
            ->where('institute_id', $session->institute_id)
            ->where('student_id', $session->student_id)
            ->whereBetween('date', $range)
            ->whereNull('deleted_at')
            ->select('date', DB::raw('MAX(CASE WHEN attendance IN ('.implode(',', self::PRESENT).') THEN 1 ELSE 0 END) AS present'))
            ->groupBy('date')
            ->get();

        $opened = $term->days_school_opened ?? DB::table('student_attendances')
            ->where('institute_id', $session->institute_id)
            ->where('branch_id', $session->branch_id)
            ->where('class_id', $session->class_id)
            ->whereBetween('date', $range)
            ->whereNull('deleted_at')
            ->distinct()
            ->count('date');

        $present = $mine->where('present', 1)->count();

        return [
            'days_school_opened' => (int) $opened,
            'days_present' => $present,
            'days_absent' => max(0, (int) $opened - $present),
        ];
    }

    /**
     * Term-by-term totals up to (and including) this exam, for classes whose
     * terms are configured in grand_final_class_exams (serial order). Null when
     * the exam is not one of the configured terms.
     */
    public function cumulative(StudentSession $session, int $examId, array $subjectRows): ?array
    {
        $terms = GrandFinalClassExam::where('class_id', $session->class_id)
            ->orderBy('serial_no')
            ->with('exam:id,name')
            ->get();

        $position = $terms->search(fn ($term) => (int) $term->exam_id === $examId);
        if ($position === false) {
            return null;
        }

        $terms = $terms->take($position + 1)->values();
        $marks = ExamMark::where('student_id', $session->student_id)
            ->whereIn('exam_id', $terms->pluck('exam_id'))
            ->get()
            ->groupBy('subject_id');

        $subjects = [];
        $allAverages = [];
        foreach ($subjectRows as $row) {
            $perTerm = [];
            $values = [];
            foreach ($terms as $term) {
                $mark = $marks->get($row['subject_id'], collect())->firstWhere('exam_id', $term->exam_id);
                $value = $mark && ! $mark->is_exempted ? (float) $mark->total_marks : null;
                $perTerm[] = ['exam_id' => $term->exam_id, 'term' => $term->exam?->name, 'total' => $value];
                if ($value !== null) {
                    $values[] = $value;
                }
            }
            $average = $values ? round(array_sum($values) / count($values), 2) : null;
            if ($average !== null) {
                $allAverages[] = $average;
            }
            $subjects[] = [
                'subject_id' => $row['subject_id'],
                'subject_name' => $row['subject_name'],
                'terms' => $perTerm,
                'cumulative_total' => $values ? round(array_sum($values), 2) : null,
                'cumulative_average' => $average,
            ];
        }

        return [
            'terms' => $terms->map(fn ($t) => ['exam_id' => $t->exam_id, 'name' => $t->exam?->name])->all(),
            'subjects' => $subjects,
            'overall_average' => $allAverages ? round(array_sum($allAverages) / count($allAverages), 2) : null,
        ];
    }

    /**
     * Written comments; the class-teacher comment falls back to the automatic
     * remark configured for the student's grade.
     */
    public function comments(int $studentId, int $examId, ?string $grade): array
    {
        $comment = StudentResultComment::where('exam_id', $examId)->where('student_id', $studentId)->first();
        $auto = $grade ? RemarkConfig::where('remark_title', $grade)->value('remarks') : null;

        return [
            'teacher_comment' => $comment?->teacher_comment ?: $auto,
            'teacher_comment_is_auto' => ! $comment?->teacher_comment && $auto !== null,
            'principal_comment' => $comment?->principal_comment,
        ];
    }
}
