<?php

namespace Modules\Academic\Services;

use App\Services\AcademicContextService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Academic\Models\Student;
use Modules\Academic\Models\StudentPromotion;
use Modules\Academic\Models\StudentPromotionBatch;
use Modules\Academic\Models\StudentSession;
use Modules\Examination\Services\GrandFinalResultService;
use Modules\Examination\Services\ResultCalculationService;

/**
 * Promotes a class from one academic session to the next.
 *
 * preview() evaluates every student against the promotion rules and suggests
 * promote / repeat / graduate; commit() applies the (possibly overridden)
 * decisions by creating a NEW student_sessions row in the target session — the
 * previous session's row is never modified, so result history survives.
 * revert() undoes a batch while the new session has no marks yet.
 *
 * Rules (all optional):
 *   basis               'annual' (grand-final result), 'exam' (one term), 'none'
 *   exam_id             term to judge on when basis = exam
 *   min_average         minimum percentage to be promoted
 *   max_failed_subjects maximum failed subjects to be promoted
 *   require_pass        the result status must be "pass"
 */
class PromotionService
{
    public function __construct(
        private readonly AcademicContextService $academicContext,
        private readonly ResultCalculationService $calculation,
        private readonly GrandFinalResultService $grandFinal,
    ) {}

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function preview(int $fromSessionId, int $classId, ?int $sectionId, array $rules, bool $graduating = false): Collection
    {
        $this->academicContext->assertClassSectionGroup($classId, $sectionId);

        $sessions = $this->fromSessions($fromSessionId, $classId, $sectionId);
        $basis = $rules['basis'] ?? 'annual';
        $examRows = $basis === 'exam' && ! empty($rules['exam_id'])
            ? $this->calculation->buildClassTermResults($classId, (int) $rules['exam_id'], $sectionId, null, null, $fromSessionId)->keyBy('student_id')
            : collect();

        return $sessions->map(function (StudentSession $session) use ($basis, $examRows, $rules, $graduating) {
            [$average, $failed, $status] = $this->performance($session, $basis, $examRows);

            $minAverage = $rules['min_average'] ?? null;
            $maxFailed = $rules['max_failed_subjects'] ?? null;
            $requirePass = ! empty($rules['require_pass']);
            $hasRules = $basis !== 'none' && ($minAverage !== null || $maxFailed !== null || $requirePass);

            // No rules: everyone is suggested for promotion. With rules, a student
            // without a result cannot be judged and is suggested to repeat.
            $meets = ! $hasRules || (
                $average !== null
                && ($minAverage === null || $average >= (float) $minAverage)
                && ($maxFailed === null || ($failed ?? 0) <= (int) $maxFailed)
                && (! $requirePass || $status === 'pass')
            );

            return [
                'student_id' => (int) $session->student_id,
                'student_session_id' => (int) $session->id,
                'student_name' => trim(($session->student->first_name ?? '').' '.($session->student->last_name ?? '')),
                'roll' => $session->roll,
                'section_id' => $session->section_id,
                'group_id' => $session->group_id,
                'average' => $average,
                'failed_subjects' => $failed,
                'result_status' => $status,
                'suggested_action' => $meets
                    ? ($graduating ? StudentPromotion::GRADUATE : StudentPromotion::PROMOTE)
                    : StudentPromotion::REPEAT,
            ];
        })->values();
    }

    /**
     * @param  array{from_session_id: int, to_session_id: ?int, class_id: int, section_id: ?int,
     *               target_class_id: ?int, target_section_id: ?int, target_group_id?: ?int,
     *               roll_strategy?: string, rules?: array, graduating?: bool, note?: ?string}  $plan
     * @param  array<int, array{student_id: int, action: string, section_id?: ?int, roll?: ?string, note?: ?string}>  $decisions
     *
     * @throws ValidationException
     */
    public function commit(array $plan, array $decisions, ?int $userId): StudentPromotionBatch
    {
        $fromSessionId = (int) $plan['from_session_id'];
        $toSessionId = $plan['to_session_id'] ? (int) $plan['to_session_id'] : null;
        $classId = (int) $plan['class_id'];
        $sectionId = $plan['section_id'] ?? null;
        $targetClassId = $plan['target_class_id'] ?? null;
        $targetSectionId = $plan['target_section_id'] ?? null;

        $this->academicContext->assertClassSectionGroup($classId, $sectionId);
        if ($targetClassId) {
            $this->academicContext->assertClassSectionGroup((int) $targetClassId, $targetSectionId ? (int) $targetSectionId : null);
        }

        $needsTarget = collect($decisions)->contains(fn ($d) => in_array($d['action'], [StudentPromotion::PROMOTE, StudentPromotion::REPEAT], true));
        if ($needsTarget && (! $toSessionId || $toSessionId === $fromSessionId)) {
            throw ValidationException::withMessages(['to_session_id' => ['Choose a different academic session to promote into.']]);
        }
        if (collect($decisions)->contains(fn ($d) => $d['action'] === StudentPromotion::PROMOTE) && ! $targetClassId) {
            throw ValidationException::withMessages(['target_class_id' => ['A target class is required to promote students.']]);
        }

        $preview = $this->preview($fromSessionId, $classId, $sectionId, $plan['rules'] ?? [], (bool) ($plan['graduating'] ?? false))->keyBy('student_id');

        $errors = [];
        foreach ($decisions as $i => $decision) {
            if (! $preview->has((int) $decision['student_id'])) {
                $errors["decisions.{$i}.student_id"] = ['This student is not in the selected class for the source session.'];
            }
        }
        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return DB::transaction(function () use ($plan, $decisions, $preview, $fromSessionId, $toSessionId, $classId, $sectionId, $targetClassId, $targetSectionId, $userId) {
            $studentIds = array_map(fn ($d) => (int) $d['student_id'], $decisions);

            // Lock the students being moved so two concurrent promotions cannot
            // both enrol the same student into the target session.
            Student::whereIn('id', $studentIds)->lockForUpdate()->get();

            $alreadyEnrolled = $toSessionId ? StudentSession::where('session_id', $toSessionId)
                ->whereIn('student_id', $studentIds)
                ->pluck('student_id')
                ->map(fn ($id) => (int) $id)
                ->all() : [];

            $conflicts = [];
            foreach ($decisions as $i => $decision) {
                if ($decision['action'] !== StudentPromotion::GRADUATE && in_array((int) $decision['student_id'], $alreadyEnrolled, true)) {
                    $conflicts["decisions.{$i}.student_id"] = ['This student already has a class in the target session.'];
                }
            }
            if ($conflicts !== []) {
                throw ValidationException::withMessages($conflicts);
            }

            $batch = StudentPromotionBatch::create([
                'institute_id' => get_institute_id(),
                'branch_id' => get_branch_id(),
                'from_session_id' => $fromSessionId,
                'to_session_id' => $toSessionId,
                'from_class_id' => $classId,
                'from_section_id' => $sectionId,
                'rules' => ($plan['rules'] ?? []) + [
                    'target_class_id' => $targetClassId,
                    'target_section_id' => $targetSectionId,
                    'roll_strategy' => $plan['roll_strategy'] ?? 'sequential',
                ],
                'status' => StudentPromotionBatch::STATUS_COMPLETED,
                'note' => $plan['note'] ?? null,
                'created_by' => $userId,
            ]);

            $rolls = [];
            $counts = array_fill_keys(StudentPromotion::ACTIONS, 0);
            $ordered = $this->orderForRolls($decisions, $preview, $plan['roll_strategy'] ?? 'sequential');

            foreach ($ordered as $decision) {
                $row = $preview->get((int) $decision['student_id']);
                $action = $decision['action'];
                $toClassId = null;
                $toSectionId = null;
                $newSessionId = null;
                $roll = null;

                if ($action === StudentPromotion::GRADUATE) {
                    Student::whereKey($row['student_id'])->update([
                        'graduated_at' => now()->toDateString(),
                        'graduated_session_id' => $fromSessionId,
                    ]);
                } else {
                    $toClassId = $action === StudentPromotion::PROMOTE ? (int) $targetClassId : $classId;
                    $toSectionId = (int) ($decision['section_id'] ?? ($action === StudentPromotion::PROMOTE ? $targetSectionId : $row['section_id']) ?? $row['section_id']);
                    $this->academicContext->assertClassSectionGroup($toClassId, $toSectionId);

                    $key = "{$toClassId}|{$toSectionId}";
                    $rolls[$key] ??= $this->nextRoll($toSessionId, $toClassId, $toSectionId);
                    $roll = isset($decision['roll']) && $decision['roll'] !== '' ? (string) $decision['roll'] : (string) $rolls[$key]++;

                    if (StudentSession::where('session_id', $toSessionId)->where('class_id', $toClassId)->where('section_id', $toSectionId)->where('roll', $roll)->exists()) {
                        throw ValidationException::withMessages(['roll' => ["Roll {$roll} is already taken in the target class/section."]]);
                    }

                    $newSessionId = StudentSession::create([
                        'institute_id' => get_institute_id(),
                        'branch_id' => get_branch_id(),
                        'session_id' => $toSessionId,
                        'student_id' => $row['student_id'],
                        'class_id' => $toClassId,
                        'section_id' => $toSectionId,
                        'group_id' => $plan['target_group_id'] ?? $row['group_id'],
                        'roll' => $roll,
                    ])->id;
                }

                StudentPromotion::create([
                    'institute_id' => get_institute_id(),
                    'branch_id' => get_branch_id(),
                    'batch_id' => $batch->id,
                    'student_id' => $row['student_id'],
                    'action' => $action,
                    'suggested_action' => $row['suggested_action'],
                    'from_student_session_id' => $row['student_session_id'],
                    'to_student_session_id' => $newSessionId,
                    'from_class_id' => $classId,
                    'from_section_id' => $row['section_id'],
                    'to_class_id' => $toClassId,
                    'to_section_id' => $toSectionId,
                    'to_roll' => $roll,
                    'average' => $row['average'],
                    'failed_subjects' => $row['failed_subjects'],
                    'note' => $decision['note'] ?? null,
                ]);
                $counts[$action]++;
            }

            $overridden = collect($decisions)->filter(fn ($d) => $d['action'] !== $preview->get((int) $d['student_id'])['suggested_action'])->count();
            $batch->update(['summary' => $counts + ['total' => count($decisions), 'overridden' => $overridden]]);

            return $batch->fresh('promotions');
        });
    }

    /**
     * Undo a batch: delete the target-session enrolments it created and clear
     * graduations — refused once any mark was recorded in the new session.
     *
     * @throws ValidationException
     */
    public function revert(StudentPromotionBatch $batch, ?int $userId): StudentPromotionBatch
    {
        if ($batch->status !== StudentPromotionBatch::STATUS_COMPLETED) {
            throw ValidationException::withMessages(['batch' => ['This promotion has already been reverted.']]);
        }

        return DB::transaction(function () use ($batch, $userId) {
            $promotions = $batch->promotions()->get();
            $studentIds = $promotions->pluck('student_id')->all();

            $hasMarks = $batch->to_session_id && DB::table('exam_marks')
                ->where('institute_id', $batch->institute_id)
                ->where('session_id', $batch->to_session_id)
                ->whereIn('student_id', $studentIds ?: [0])
                ->exists();
            if ($hasMarks) {
                throw ValidationException::withMessages(['batch' => ['Marks have already been recorded in the new session; this promotion can no longer be reverted.']]);
            }

            foreach ($promotions as $promotion) {
                if ($promotion->to_student_session_id) {
                    StudentSession::withTrashed()->whereKey($promotion->to_student_session_id)->forceDelete();
                }
                if ($promotion->action === StudentPromotion::GRADUATE) {
                    Student::whereKey($promotion->student_id)->update(['graduated_at' => null, 'graduated_session_id' => null]);
                }
            }

            $batch->update(['status' => StudentPromotionBatch::STATUS_REVERTED, 'reverted_by' => $userId, 'reverted_at' => now()]);

            return $batch->fresh();
        });
    }

    private function fromSessions(int $fromSessionId, int $classId, ?int $sectionId): Collection
    {
        return $this->academicContext->applyTenantScope(StudentSession::query())
            ->where('session_id', $fromSessionId)
            ->where('class_id', $classId)
            ->when($sectionId, fn ($q) => $q->where('section_id', $sectionId))
            ->with('student:id,first_name,last_name,graduated_at')
            ->orderByRaw('CAST(roll AS UNSIGNED), roll')
            ->get();
    }

    /**
     * @return array{0: ?float, 1: ?int, 2: ?string} [average %, failed subjects, status]
     */
    private function performance(StudentSession $session, string $basis, Collection $examRows): array
    {
        if ($basis === 'none') {
            return [null, null, null];
        }

        if ($basis === 'exam') {
            $row = $examRows->get($session->student_id);

            return $row && $row['status'] !== 'not_published'
                ? [(float) $row['percentage'], (int) $row['failed_subject_count'], $row['status']]
                : [null, null, null];
        }

        $annual = $this->grandFinal->calculateForStudentSession($session);
        if (! ($annual['configured'] ?? false)) {
            return [null, null, null];
        }

        $average = $annual['final_score'] ?? (($annual['final_full_marks'] ?? 0) > 0
            ? round(($annual['final_obtained'] / $annual['final_full_marks']) * 100, 2)
            : null);

        return [$average !== null ? (float) $average : null, null, ($annual['grade_name'] ?? 'F') === 'F' ? 'fail' : 'pass'];
    }

    private function orderForRolls(array $decisions, Collection $preview, string $strategy): array
    {
        if ($strategy !== 'merit') {
            return $decisions; // sequential: keep the class's current roll order
        }

        usort($decisions, fn ($a, $b) => ($preview->get((int) $b['student_id'])['average'] ?? -1) <=> ($preview->get((int) $a['student_id'])['average'] ?? -1));

        return $decisions;
    }

    private function nextRoll(int $sessionId, int $classId, int $sectionId): int
    {
        $max = StudentSession::where('session_id', $sessionId)
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->pluck('roll')
            ->map(fn ($roll) => (int) $roll)
            ->max();

        return ((int) $max) + 1;
    }
}
