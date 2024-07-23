<?php

namespace Modules\Examination\Services;

use Illuminate\Support\Collection;
use Modules\Examination\Models\ClassExam;

class PositionCalculationService
{
    public const METHOD_TOTAL_MARKS = 'total_marks';

    public const METHOD_GRADE_POINT = 'grade_point';

    public const FAILED_WITH_OTHERS = 'with_others';

    public const FAILED_AFTER_PASSERS = 'after_passers';

    public const FAILED_UNRANKED = 'unranked';

    /**
     * Competition ranking: ties share a position, next rank skips
     * (1, 2, 2, 4). Failed-student policy is configurable.
     *
     * @param  Collection<int, array>  $results  each item must include student_id, total_marks, gpa, status
     */
    public function rank(Collection $results, string $method = self::METHOD_TOTAL_MARKS, string $failedPolicy = self::FAILED_WITH_OTHERS): Collection
    {
        $sortKey = $method === self::METHOD_GRADE_POINT ? 'gpa' : 'total_marks';

        $assign = function (Collection $set) use ($sortKey): array {
            $sorted = $set->sortByDesc(fn (array $row) => (float) ($row[$sortKey] ?? 0))->values();
            $position = 0;
            $previousValue = null;
            $map = [];

            foreach ($sorted as $index => $row) {
                $value = (float) ($row[$sortKey] ?? 0);
                if ($value !== $previousValue) {
                    $position = $index + 1;
                    $previousValue = $value;
                }
                $map[$row['student_id']] = $position;
            }

            return $map;
        };

        if ($failedPolicy === self::FAILED_UNRANKED) {
            $passers = $results->filter(fn (array $row) => ($row['status'] ?? '') === 'pass');
            $rankMap = $assign($passers);

            return $results->map(function (array $row) use ($rankMap) {
                $row['position'] = $rankMap[$row['student_id']] ?? null;

                return $row;
            });
        }

        if ($failedPolicy === self::FAILED_AFTER_PASSERS) {
            $passers = $results->filter(fn (array $row) => ($row['status'] ?? '') === 'pass');
            $failed = $results->filter(fn (array $row) => ($row['status'] ?? '') !== 'pass');
            $passMap = $assign($passers);
            $failedMap = $assign($failed);
            $offset = $passers->count();

            return $results->map(function (array $row) use ($passMap, $failedMap, $offset) {
                if (($row['status'] ?? '') === 'pass') {
                    $row['position'] = $passMap[$row['student_id']] ?? null;
                } else {
                    $failedPosition = $failedMap[$row['student_id']] ?? null;
                    $row['position'] = $failedPosition !== null ? $offset + $failedPosition : null;
                }

                return $row;
            });
        }

        $rankMap = $assign($results);

        return $results->map(function (array $row) use ($rankMap) {
            $row['position'] = $rankMap[$row['student_id']] ?? null;

            return $row;
        });
    }

    public function resolveMethodForClassExam(int $classId, int $examId): string
    {
        $classExam = ClassExam::where('class_id', $classId)
            ->where('exam_id', $examId)
            ->with('meritType')
            ->first();

        $type = strtolower((string) ($classExam?->meritType?->type ?? ''));

        if (str_contains($type, 'grade')) {
            return self::METHOD_GRADE_POINT;
        }

        return self::METHOD_TOTAL_MARKS;
    }
}
