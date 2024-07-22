<?php

namespace Modules\Examination\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Examination\Models\ExamMark;
use Modules\Examination\Models\MarkConfig;
use Modules\Examination\Models\RemarkConfig;

class MarkEntryService
{
    /**
     * Mark configuration for one (subject, exam), memoised for the life of this
     * instance.
     *
     * saveSectionWiseMarks() is called once per student from a loop in which
     * subject_id and exam_id never change (markStoreSectionWise saves one
     * subject's marks for a whole section), yet each call re-ran the same two
     * config lookups. At two queries each that was four provably identical
     * round-trips per student -- ~240 of the ~600 queries a 60-student save cost.
     *
     * @var array<string, Collection>
     */
    private array $componentCache = [];

    /** @var array<string, string|null> teacher remark keyed by grade name */
    private array $remarkCache = [];

    public function __construct(
        private readonly GradeCalculationService $gradeCalculationService,
        private readonly ExamContextService $examContextService,
        private readonly ResultPublicationService $publications,
    ) {}

    /**
     * The maximum total marks allowed for a subject's mark entry.
     * Sums the subject's configured MarkConfigExamCode components for the
     * exam when configured; falls back to 100 when nothing is configured
     * yet, matching the previous hard-coded behavior for those classes.
     */
    public function resolveMaxMarks(int $subjectId, int $examId): float
    {
        $configuredTotal = $this->resolveComponents($subjectId, $examId)
            ->pluck('mark_config_exam_code.total_marks')
            ->filter()
            ->sum();

        return $configuredTotal > 0 ? (float) $configuredTotal : 100.0;
    }

    public function resolvePassMarks(int $subjectId, int $examId): float
    {
        $configuredPass = $this->resolveComponents($subjectId, $examId)
            ->pluck('mark_config_exam_code.pass_mark')
            ->filter(fn ($value) => $value !== null)
            ->sum();

        return $configuredPass > 0 ? (float) $configuredPass : 33.0;
    }

    public function resolveComponents(int $subjectId, int $examId)
    {
        return $this->componentCache[$subjectId.'|'.$examId] ??= MarkConfig::where('subject_id', $subjectId)
            ->where('exam_id', $examId)
            ->with('mark_config_exam_code')
            ->get();
    }

    /**
     * The teacher remark configured for a grade name.
     *
     * remark_configs holds one row per grade band — a handful of rows — but this
     * was queried once per student saved, since every student in a section
     * resolves to one of the same few grades.
     */
    private function remarkForGrade(?string $gradeName): ?string
    {
        $key = (string) $gradeName;

        if (! array_key_exists($key, $this->remarkCache)) {
            $this->remarkCache[$key] = RemarkConfig::where('remark_title', $gradeName)->value('remarks');
        }

        return $this->remarkCache[$key];
    }

    /**
     * Drop the memoised mark configuration. Only needed by a request that both
     * rewrites MarkConfig rows and then reads marks back in the same process.
     */
    public function flushConfigCache(): void
    {
        $this->componentCache = [];
        $this->remarkCache = [];
    }

    /**
     * Save one student's section-wise marks for a subject/exam.
     *
     * @param  array  $identifiers  institute_id, branch_id, session_id, student_id, class_id, group_id, subject_id, exam_id
     * @param  array<int|string, mixed>  $markValuesByQuestion  question number (1-6) => mark value
     *
     * @throws \Exception when the total exceeds the resolved max marks
     */
    public function saveSectionWiseMarks(array $identifiers, array $markValuesByQuestion, array $meta = []): ExamMark
    {
        // A locked (approved / published) result must not change underneath the
        // printed report cards — this is the single write path for manual entry,
        // imports and CBT transfers.
        $this->publications->assertUnlocked(
            (int) $identifiers['exam_id'],
            (int) $identifiers['class_id'],
            isset($identifiers['institute_id']) ? (int) $identifiers['institute_id'] : null,
            isset($identifiers['branch_id']) ? (int) $identifiers['branch_id'] : null,
        );

        $isAbsent = (bool) ($meta['is_absent'] ?? false);
        $isExempted = (bool) ($meta['is_exempted'] ?? false);
        $source = $meta['source'] ?? ExamMark::SOURCE_MANUAL;

        $marksData = [];
        $totalMarks = 0.0;

        foreach ($markValuesByQuestion as $questionNumber => $mark) {
            $questionNumber = (int) $questionNumber;

            if ($questionNumber < 1 || $questionNumber > 6) {
                continue;
            }

            $markValue = (float) ($mark ?? 0);
            $marksData["mark{$questionNumber}"] = $markValue;
            $totalMarks += $markValue;
        }

        $maxMarks = $this->resolveMaxMarks((int) $identifiers['subject_id'], (int) $identifiers['exam_id']);
        $passMarks = $this->resolvePassMarks((int) $identifiers['subject_id'], (int) $identifiers['exam_id']);

        if (! $isAbsent && ! $isExempted && $totalMarks > $maxMarks) {
            throw new \Exception(
                "Total marks must not exceed {$maxMarks}. Student ID {$identifiers['student_id']} has {$totalMarks}"
            );
        }

        if ($isAbsent) {
            $totalMarks = 0.0;
            $percentage = 0.0;
            $status = ExamMark::STATUS_ABSENT;
            $gradeName = 'F';
            $gradePoint = 0.0;
        } elseif ($isExempted) {
            $percentage = 0.0;
            $status = ExamMark::STATUS_EXEMPTED;
            $gradeName = $meta['grade'] ?? 'EX';
            $gradePoint = (float) ($meta['grade_point'] ?? 0);
        } else {
            $percentage = $this->gradeCalculationService->calculatePercentage($totalMarks, $maxMarks);
            $grade = $this->gradeCalculationService->resolveGrade(
                (int) $identifiers['class_id'],
                $percentage,
                isset($identifiers['institute_id']) ? (int) $identifiers['institute_id'] : null,
            );
            $gradeName = $grade->grade_name ?? 'F';
            $gradePoint = (float) ($grade->grade_point ?? 0);
            $status = ($totalMarks < $passMarks || $gradeName === 'F')
                ? ExamMark::STATUS_FAIL
                : ExamMark::STATUS_PASS;
        }

        $remarks = $meta['remarks'] ?? $this->remarkForGrade($gradeName);

        $data = array_merge($marksData, [
            'class_id' => (int) $identifiers['class_id'],
            'group_id' => $this->examContextService->resolveGroupId(
                isset($identifiers['group_id']) ? (int) $identifiers['group_id'] : null
            ),
            'total_marks' => $totalMarks,
            'percentage' => $percentage,
            'grade' => $gradeName,
            'grade_point' => $gradePoint,
            'status' => $status,
            'remarks' => $remarks,
            'is_absent' => $isAbsent,
            'is_exempted' => $isExempted,
            'source' => $source,
        ]);

        $lookupKeys = $this->examContextService->markLookupKeys($identifiers);
        $existing = ExamMark::where($lookupKeys)->first();

        if ($existing) {
            $existing->update($data);

            // No ->fresh() re-query: update() has already applied $data to the
            // model, and every value the callers read back (total_marks, grade,
            // grade_point, percentage) is cast decimal:2, so the in-memory model
            // formats identically to a re-read one. That was one extra SELECT per
            // student on every save.
            return $existing;
        }

        return ExamMark::create(array_merge($lookupKeys, $data));
    }
}
