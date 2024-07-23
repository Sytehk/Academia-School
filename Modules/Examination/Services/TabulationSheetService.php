<?php

namespace Modules\Examination\Services;

use Illuminate\Support\Collection;
use Modules\Academic\Models\StudentSession;
use Modules\Academic\Models\Subject;
use Modules\Examination\Models\ExamMark;
use Modules\Examination\Models\MarkConfig;
use Modules\Examination\Models\RemarkConfig;

class TabulationSheetService
{
    public function __construct(
        private readonly ResultCalculationService $resultCalculationService,
        private readonly GradeCalculationService $gradeCalculationService,
        private readonly PositionCalculationService $positionCalculationService,
        private readonly MarkEntryService $markEntryService,
        private readonly ExamContextService $examContextService,
    ) {}

    /**
     * Build a class-wide tabulation / broad sheet with dynamic subject columns.
     *
     * @param  bool  $blankMarks  When true, list students/subjects with empty mark cells for manual entry.
     * @param  bool  $showPosition  When false, calculated results are still shown but each
     *                              student's rank is withheld — used to hand teachers a sheet for proof-reading marks
     *                              before the result is finalized without leaking who came first (FEAT-006).
     */
    public function build(int $classId, int $examId, ?int $sectionId = null, ?int $groupId = null, bool $blankMarks = false, bool $showPosition = true): array
    {
        $query = StudentSession::with(['student.user', 'session', 'class', 'section', 'group'])
            ->where('class_id', $classId);

        $this->examContextService->applySessionFilters($query, $sectionId, $groupId);

        $sessions = $query->orderByRaw('CAST(roll AS UNSIGNED), roll')->get();
        $resolvedGroupId = $this->examContextService->normalizeGroupId($groupId) ?? $sessions->first()?->group_id;
        $subjects = $this->resolveSubjects($classId, $resolvedGroupId);

        $studentIds = $sessions->pluck('student_id')->all();
        $marks = collect();

        if (! $blankMarks) {
            $marks = ExamMark::where('exam_id', $examId)
                ->where('class_id', $classId)
                ->whereIn('student_id', $studentIds ?: [0])
                ->get()
                ->groupBy('student_id');
        }

        $configs = MarkConfig::with('mark_config_exam_code')
            ->where('class_id', $classId)
            ->where('exam_id', $examId)
            ->when($resolvedGroupId, fn ($query) => $query->where('group_id', $resolvedGroupId))
            ->get()
            ->groupBy('subject_id');

        $fullMarksBySubject = [];
        $passMarksBySubject = [];
        $componentsBySubject = [];

        foreach ($subjects as $subject) {
            $subjectConfigs = $configs->get($subject->id, collect());
            $full = (float) $subjectConfigs->sum(fn ($row) => (float) ($row->mark_config_exam_code?->total_marks ?? 0));
            $pass = (float) $subjectConfigs->sum(fn ($row) => (float) ($row->mark_config_exam_code?->pass_mark ?? 0));
            $fullMarksBySubject[$subject->id] = $full > 0 ? $full : $this->markEntryService->resolveMaxMarks((int) $subject->id, $examId);
            $passMarksBySubject[$subject->id] = $pass > 0 ? $pass : $this->markEntryService->resolvePassMarks((int) $subject->id, $examId);
            $componentsBySubject[$subject->id] = $subjectConfigs->values()->map(function ($row, $index) {
                return [
                    'slot' => $index + 1,
                    'title' => $row->mark_config_exam_code?->title ?? ('M'.($index + 1)),
                    'full_marks' => (float) ($row->mark_config_exam_code?->total_marks ?? 0),
                    'pass_marks' => (float) ($row->mark_config_exam_code?->pass_mark ?? 0),
                ];
            })->all();
        }

        $remarksByGrade = RemarkConfig::query()->get()->keyBy('remark_title');
        $rows = collect();

        foreach ($sessions as $session) {
            if ($blankMarks) {
                $subjectCells = [];
                foreach ($subjects as $subject) {
                    $components = $componentsBySubject[$subject->id] ?? [];
                    $componentValues = collect($components)->map(fn ($component) => [
                        'title' => $component['title'],
                        'obtained' => null,
                    ])->all();

                    $subjectCells[$subject->id] = [
                        'obtained' => null,
                        'grade' => null,
                        'grade_point' => null,
                        'status' => 'blank',
                        'components' => $componentValues,
                    ];
                }

                $rows->push([
                    'student_id' => $session->student_id,
                    'student_session' => $session,
                    'student_name' => trim(($session->student->first_name ?? '').' '.($session->student->last_name ?? '')),
                    'roll' => $session->roll,
                    'registration_no' => $session->student?->registration_no,
                    'subject_cells' => $subjectCells,
                    'total_marks' => null,
                    'total_full_marks' => array_sum($fullMarksBySubject),
                    'percentage' => null,
                    'gpa' => null,
                    'grade' => null,
                    'status' => 'blank',
                    'failed_subjects' => [],
                    'failed_subject_count' => null,
                    'remarks' => null,
                    'position' => null,
                ]);

                continue;
            }

            $studentMarks = $marks->get($session->student_id, collect())->keyBy('subject_id');
            $subjectCells = [];
            $totalObtained = 0.0;
            $totalFull = 0.0;
            $totalPoints = 0.0;
            $counted = 0;
            $failed = [];
            $hasFail = false;

            foreach ($subjects as $subject) {
                $mark = $studentMarks->get($subject->id);
                $fullMarks = $fullMarksBySubject[$subject->id];
                $passMarks = $passMarksBySubject[$subject->id];
                $components = $componentsBySubject[$subject->id];
                $componentValues = [];

                foreach ($components as $component) {
                    $slot = $component['slot'];
                    $componentValues[] = [
                        'title' => $component['title'],
                        'obtained' => $mark ? (float) $mark->{"mark{$slot}"} : null,
                    ];
                }

                if (! $mark) {
                    $subjectCells[$subject->id] = [
                        'obtained' => null,
                        'grade' => null,
                        'grade_point' => null,
                        'status' => 'missing',
                        'components' => $componentValues,
                    ];

                    continue;
                }

                $obtained = (float) $mark->total_marks;
                $status = $mark->status ?: (($obtained < $passMarks || $mark->grade === 'F') ? 'fail' : 'pass');
                if ($mark->is_absent) {
                    $status = 'absent';
                }

                if (in_array($status, ['fail', 'absent'], true)) {
                    $hasFail = true;
                    $failed[] = $subject->subject_name;
                }

                if (! $mark->is_exempted) {
                    $totalObtained += $obtained;
                    $totalFull += $fullMarks;
                    $totalPoints += (float) $mark->grade_point;
                    $counted++;
                }

                $subjectCells[$subject->id] = [
                    'obtained' => $obtained,
                    'grade' => $mark->grade,
                    'grade_point' => (float) $mark->grade_point,
                    'status' => $status,
                    'components' => $componentValues,
                ];
            }

            $gpa = $counted > 0 ? round($totalPoints / $counted, 2) : 0.0;
            $percentage = $this->gradeCalculationService->calculatePercentage($totalObtained, $totalFull);
            $status = $counted === 0 ? 'not_published' : ($hasFail ? 'fail' : 'pass');
            $overallGrade = $this->gradeCalculationService->resolveGrade($classId, $percentage, $session->institute_id);

            $rows->push([
                'student_id' => $session->student_id,
                'student_session' => $session,
                'student_name' => trim(($session->student->first_name ?? '').' '.($session->student->last_name ?? '')),
                'roll' => $session->roll,
                'registration_no' => $session->student?->registration_no,
                'subject_cells' => $subjectCells,
                'total_marks' => $totalObtained,
                'total_full_marks' => $totalFull,
                'percentage' => $percentage,
                'gpa' => $gpa,
                'grade' => $overallGrade->grade_name ?? ($hasFail ? 'F' : 'N/A'),
                'status' => $status,
                'failed_subjects' => $failed,
                'failed_subject_count' => count($failed),
                'remarks' => $remarksByGrade->get($overallGrade->grade_name ?? '')?->remarks,
            ]);
        }

        $ranked = $rows;
        if (! $blankMarks && $showPosition) {
            $settings = $this->resultCalculationService->resultCardSettings();
            $method = $this->positionCalculationService->resolveMethodForClassExam($classId, $examId);
            $ranked = $this->positionCalculationService->rank(
                $rows,
                $method,
                $settings['rank_failed_students'] ?? PositionCalculationService::FAILED_WITH_OTHERS
            );
        }

        $first = $sessions->first();
        $subjectColumns = $subjects->map(function ($subject) use ($fullMarksBySubject, $componentsBySubject) {
            return [
                'id' => $subject->id,
                'name' => $subject->subject_name,
                'code' => $subject->subject_code,
                'full_marks' => $fullMarksBySubject[$subject->id] ?? 100,
                'components' => $componentsBySubject[$subject->id] ?? [],
            ];
        })->values();

        $paper = $subjectColumns->count() > 10 ? 'a3' : 'a4';

        return [
            'class_id' => $classId,
            'exam_id' => $examId,
            'class_name' => $first?->class?->class_name,
            'section_name' => $sectionId ? ($first?->section?->section_name) : 'All',
            'group_name' => $first?->group?->group_name,
            'session_name' => $first?->session?->session,
            'student_count' => $ranked->count(),
            'subjects' => $subjectColumns,
            'rows' => $ranked->values(),
            'paper' => $paper,
            'blank_marks' => $blankMarks,
            'show_position' => $showPosition,
        ];
    }

    private function resolveSubjects(int $classId, ?int $groupId): Collection
    {
        $subjects = $this->resultCalculationService->subjectsForClassGroup($classId, $groupId);

        if ($subjects->isNotEmpty()) {
            return $subjects;
        }

        return Subject::where('class_id', $classId)
            ->orderBy('id')
            ->get(['id', 'subject_name', 'subject_code', 'full_mark', 'pass_mark', 'group_id', 'class_id']);
    }
}
