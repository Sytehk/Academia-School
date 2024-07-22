<?php

namespace Modules\Examination\Services;

use Illuminate\Support\Collection;
use Modules\Examination\Models\ExamGrade;
use Modules\Examination\Models\Grade;

/**
 * Resolves grade bands.
 *
 * Both scales are small, per-class/per-institute reference tables, but the
 * callers ask for one student at a time inside a loop: TabulationSheetService
 * calls resolveGrade() once per student in the class, and MarkEntryService once
 * per student being saved. Each of those used to be one or two fresh queries, so
 * a 60-student class cost 60-120 identical round-trips.
 *
 * The row sets are therefore loaded once per (class, institute) per instance and
 * the band is matched in PHP. The service is resolved from the container per
 * request, so the memo lives exactly as long as one request.
 */
class GradeCalculationService
{
    /** @var array<string, Collection<int, ExamGrade>> */
    private array $examGrades = [];

    /** @var array<string, Collection<int, Grade>> */
    private array $grades = [];

    /**
     * Resolve the grade for a given class and marks/percentage value.
     * Prefers the class-specific grading scale (ExamGrade) when one is
     * configured for the class, falling back to the global Grade scale.
     *
     * $instituteId is optional and, when provided, scopes the global-Grade
     * fallback to that institute (neither Grade nor ExamGrade carry an
     * automatic tenant scope), matching this app's other tenant-scoped
     * lookups.
     */
    public function resolveGrade(int $classId, float $marksOrPercentage, ?int $instituteId = null): ?Grade
    {
        $examGrade = $this->examGradeScale($classId, $instituteId)
            ->first(fn ($row) => $this->within($row->number_low, $row->number_high, $marksOrPercentage));

        if ($examGrade) {
            return new Grade([
                'grade_name' => $examGrade->grade_name,
                'grade_number' => $examGrade->grade_number,
                'grade_point' => $examGrade->grade_point,
                'grade_range' => $examGrade->grade_range,
                'number_high' => $examGrade->number_high,
                'number_low' => $examGrade->number_low,
                'point_high' => $examGrade->point_high,
                'point_low' => $examGrade->point_low,
                'priority' => $examGrade->priority,
            ]);
        }

        return $this->gradeScale($instituteId)
            ->first(fn ($row) => $this->within($row->number_low, $row->number_high, $marksOrPercentage));
    }

    /**
     * Resolve a grade by GPA/grade-point rather than by percentage. Shares the
     * memoised scale with resolveGrade() so ResultCalculationService no longer
     * issues its own query per student.
     */
    public function resolveGradeByPoint(float $gradePoint, ?int $instituteId = null): ?Grade
    {
        return $this->gradeScale($instituteId)
            ->first(fn ($row) => $this->within($row->point_low, $row->point_high, $gradePoint));
    }

    public function calculatePercentage(float $obtained, float $fullMarks): float
    {
        if ($fullMarks <= 0) {
            return 0.0;
        }

        return round(($obtained / $fullMarks) * 100, 2);
    }

    /**
     * A NULL bound never matched in SQL (NULL <= x is NULL, not true). Casting a
     * NULL to float in PHP would yield 0.0 and start matching, so the boundary
     * rows are skipped explicitly to keep the old behaviour exactly.
     */
    private function within(mixed $low, mixed $high, float $value): bool
    {
        if ($low === null || $high === null) {
            return false;
        }

        return (float) $low <= $value && (float) $high >= $value;
    }

    /** @return Collection<int, ExamGrade> */
    private function examGradeScale(int $classId, ?int $instituteId): Collection
    {
        $key = $classId.'|'.($instituteId ?? 'null');

        // orderBy('id') reproduces the implicit order the previous ->first()
        // relied on, so ambiguous/overlapping bands still resolve identically.
        return $this->examGrades[$key] ??= ExamGrade::where('class_model_id', $classId)
            ->when($instituteId, fn ($query) => $query->where('institute_id', $instituteId))
            ->orderBy('id')
            ->get();
    }

    /** @return Collection<int, Grade> */
    private function gradeScale(?int $instituteId): Collection
    {
        $key = (string) ($instituteId ?? 'null');

        return $this->grades[$key] ??= Grade::when($instituteId, fn ($query) => $query->where('institute_id', $instituteId))
            ->orderBy('id')
            ->get();
    }
}
