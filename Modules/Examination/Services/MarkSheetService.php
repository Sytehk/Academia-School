<?php

namespace Modules\Examination\Services;

use App\Services\AcademicContextService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Academic\Models\StudentSession;
use Modules\Academic\Models\Subject;
use Modules\Examination\Models\ExamMark;

/**
 * Round-trippable mark sheet: export() produces exactly the columns import()
 * reads back (student_id, roll, student_name, mark_1..mark_N, is_absent), so a
 * teacher can download a class/subject sheet, fill it in offline and upload it.
 *
 * Every row is re-validated against the caller's institute/branch, the active
 * session and the class/section/group — the file is never trusted for ids.
 */
class MarkSheetService
{
    public function __construct(
        private readonly AcademicContextService $academicContext,
        private readonly MarkEntryService $markEntryService,
    ) {}

    /**
     * @return array{components: Collection, students: Collection}
     */
    public function sheet(int $examId, int $classId, int $subjectId, ?int $sectionId = null, ?int $groupId = null): array
    {
        $this->assertContext($classId, $subjectId, $sectionId, $groupId);

        $components = $this->components($subjectId, $examId);

        $students = $this->academicContext->studentSessionQuery($classId, $sectionId, $groupId)
            ->with('student:id,first_name,last_name')
            ->orderByRaw('CAST(roll AS UNSIGNED), roll')
            ->get();

        $existing = ExamMark::where('exam_id', $examId)
            ->where('subject_id', $subjectId)
            ->whereIn('student_id', $students->pluck('student_id'))
            ->when($this->academicContext->currentSessionId(), fn ($q, $s) => $q->where('session_id', $s))
            ->get()
            ->keyBy('student_id');

        $rows = $students->map(function (StudentSession $session) use ($components, $existing) {
            $mark = $existing->get($session->student_id);
            $row = [
                'student_id' => $session->student_id,
                'roll' => $session->roll,
                'student_name' => trim(($session->student->first_name ?? '').' '.($session->student->last_name ?? '')),
            ];
            foreach ($components->keys() as $slot) {
                $row['mark_'.$slot] = $mark ? (float) $mark->{'mark'.$slot} : null;
            }
            $row['is_absent'] = $mark && $mark->is_absent ? 1 : 0;

            return $row;
        });

        return ['components' => $components, 'students' => $rows->values()];
    }

    /**
     * Configured components for a subject/exam keyed by mark slot (1..6).
     * Falls back to a single 100-mark slot when nothing is configured, matching
     * MarkEntryService::resolveMaxMarks().
     *
     * @return Collection<int, array{title: string, total_marks: float, pass_mark: float|null}>
     */
    public function components(int $subjectId, int $examId): Collection
    {
        // Same slot numbering as ResultCalculationService::componentValues():
        // the n-th MarkConfig row (default id order) is stored in mark{n}.
        $configured = $this->markEntryService->resolveComponents($subjectId, $examId)->values()->take(6);

        if ($configured->isEmpty()) {
            return collect([1 => ['title' => 'Marks', 'total_marks' => 100.0, 'pass_mark' => null]]);
        }

        return $configured->mapWithKeys(fn ($config, $index) => [
            $index + 1 => [
                'title' => $config->mark_config_exam_code->title ?? ('Mark '.($index + 1)),
                'total_marks' => (float) ($config->mark_config_exam_code->total_marks ?? 100),
                'pass_mark' => isset($config->mark_config_exam_code->pass_mark) ? (float) $config->mark_config_exam_code->pass_mark : null,
            ],
        ]);
    }

    /**
     * Validate and save imported rows. All-or-nothing: any invalid row aborts the
     * whole import with a per-row error list.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return array{saved: int, rows: array<int, array<string, mixed>>}
     *
     * @throws ValidationException
     */
    public function import(array $rows, int $examId, int $classId, int $subjectId, ?int $sectionId = null, ?int $groupId = null): array
    {
        $this->assertContext($classId, $subjectId, $sectionId, $groupId);
        $components = $this->components($subjectId, $examId);

        $rows = array_values(array_filter($rows, fn ($row) => ! empty($row['student_id'])));
        if ($rows === []) {
            throw ValidationException::withMessages(['file' => [_lang('The file contains no student rows.')]]);
        }

        $studentIds = array_map(fn ($row) => (int) $row['student_id'], $rows);
        if (count($studentIds) !== count(array_unique($studentIds))) {
            throw ValidationException::withMessages(['file' => [_lang('A student appears more than once in the file.')]]);
        }

        $this->academicContext->assertStudentsInClassSection($studentIds, $classId, $sectionId, $groupId);

        $errors = [];
        foreach ($rows as $index => $row) {
            $line = $index + 2; // heading row is line 1
            foreach ($components as $slot => $component) {
                $value = $row['mark_'.$slot] ?? null;
                if ($value === null || $value === '') {
                    continue;
                }
                if (! is_numeric($value) || (float) $value < 0) {
                    $errors["row_{$line}"][] = "mark_{$slot} must be a non-negative number.";
                } elseif ((float) $value > $component['total_marks']) {
                    $errors["row_{$line}"][] = "mark_{$slot} ({$component['title']}) cannot exceed {$component['total_marks']}.";
                }
            }
            foreach (array_keys($row) as $key) {
                if (preg_match('/^mark_(\d+)$/', (string) $key, $m) && ! $components->has((int) $m[1])
                    && $row[$key] !== null && $row[$key] !== '') {
                    $errors["row_{$line}"][] = "{$key} is not a configured component for this subject.";
                }
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        $identifierBase = [
            'institute_id' => get_institute_id(),
            'branch_id' => get_branch_id(),
            'session_id' => $this->academicContext->currentSessionId(),
            'class_id' => $classId,
            'group_id' => $groupId,
            'subject_id' => $subjectId,
            'exam_id' => $examId,
        ];

        $saved = [];
        DB::transaction(function () use ($rows, $components, $identifierBase, &$saved) {
            foreach ($rows as $row) {
                $values = [];
                foreach ($components->keys() as $slot) {
                    $values[$slot] = $row['mark_'.$slot] ?? null;
                }

                $mark = $this->markEntryService->saveSectionWiseMarks(
                    $identifierBase + ['student_id' => (int) $row['student_id']],
                    $values,
                    [
                        'is_absent' => filter_var($row['is_absent'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'source' => ExamMark::SOURCE_IMPORT,
                    ]
                );

                $saved[] = [
                    'student_id' => (int) $row['student_id'],
                    'total_marks' => (float) $mark->total_marks,
                    'grade' => $mark->grade,
                    'status' => $mark->status,
                ];
            }
        });

        return ['saved' => count($saved), 'rows' => $saved];
    }

    /**
     * @throws ValidationException
     */
    private function assertContext(int $classId, int $subjectId, ?int $sectionId, ?int $groupId): void
    {
        $this->academicContext->assertClassSectionGroup($classId, $sectionId, $groupId);

        if (! Subject::where('id', $subjectId)->where('class_id', $classId)->exists()) {
            throw ValidationException::withMessages([
                'subject_id' => [_lang('The selected subject does not belong to the chosen class.')],
            ]);
        }
    }
}
