<?php

namespace Modules\Examination\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Academic\Models\StudentSession;
use Modules\Examination\Exceptions\OnlineExamException;
use Modules\Examination\Models\OnlineExam;
use Modules\Examination\Models\OnlineExamAttempt;
use Modules\Examination\Models\OnlineExamAttemptQuestion;
use Modules\Examination\Models\OnlineExamQuestion;

/**
 * The CBT engine: dealing questions to an attempt, the timer, saving answers,
 * automatic marking, manual grading of descriptive answers, auto-submission of
 * expired attempts and choosing which attempt counts.
 *
 * Every attempt is dealt a fixed set of questions (pool draws / random N) with
 * a fixed question and option order, stored in online_exam_attempt_questions,
 * so a reload never reshuffles and marking scores exactly what was shown.
 */
class OnlineExamScoringService
{
    public function convertScore(float $obtained, float $onlineFullMarks, float $academicFullMarks, bool $enabled): float
    {
        if (! $enabled || $onlineFullMarks <= 0) {
            return round($obtained, 2);
        }

        return round(($obtained / $onlineFullMarks) * $academicFullMarks, 2);
    }

    /**
     * Whether a student (by their current-session enrolment) may sit this exam:
     * same class, and — when the exam targets one — the same section and group.
     */
    public function isEligible(OnlineExam $onlineExam, ?StudentSession $session): bool
    {
        if (! $session || (int) $onlineExam->class_id !== (int) $session->class_id) {
            return false;
        }

        if ($onlineExam->section_id && (int) $onlineExam->section_id !== (int) $session->section_id) {
            return false;
        }

        if ($onlineExam->group_id && (int) $onlineExam->group_id !== (int) $session->group_id) {
            return false;
        }

        return true;
    }

    /**
     * Start a new attempt, or resume the student's running one.
     *
     * @throws OnlineExamException
     */
    public function startAttempt(OnlineExam $onlineExam, int $studentId, ?int $userId = null): OnlineExamAttempt
    {
        return DB::transaction(function () use ($onlineExam, $studentId, $userId) {
            // Serialise concurrent "start" clicks for the same exam so a student
            // can never end up with two running attempts.
            $exam = OnlineExam::whereKey($onlineExam->id)->lockForUpdate()->firstOrFail();

            $existing = OnlineExamAttempt::where('online_exam_id', $exam->id)
                ->where('student_id', $studentId)
                ->where('status', OnlineExamAttempt::STATUS_STARTED)
                ->latest('id')
                ->first();

            if ($existing && ! $existing->isExpired()) {
                $this->ensureQuestionsAssigned($existing, $exam);

                return $existing;
            }

            if ($existing) {
                $this->finalizeAttempt($existing, $existing->answers ?? [], true);
            }

            if (! $exam->isOpen()) {
                throw new OnlineExamException($exam->hasEnded()
                    ? 'This online exam has ended.'
                    : 'This online exam is not currently available.');
            }

            $attemptCount = OnlineExamAttempt::where('online_exam_id', $exam->id)
                ->where('student_id', $studentId)
                ->count();
            $submittedCount = OnlineExamAttempt::where('online_exam_id', $exam->id)
                ->where('student_id', $studentId)
                ->whereIn('status', [OnlineExamAttempt::STATUS_SUBMITTED, OnlineExamAttempt::STATUS_AUTO_SUBMITTED])
                ->count();

            if ($submittedCount >= max(1, (int) $exam->attempt_limit)) {
                throw new OnlineExamException('You have already reached the maximum number of attempts.');
            }

            $expiresAt = now()->addMinutes(max(1, (int) $exam->duration_minutes));
            if ($exam->end_at && $exam->end_at->lt($expiresAt)) {
                $expiresAt = $exam->end_at->copy();
            }

            $attempt = OnlineExamAttempt::create([
                'institute_id' => $exam->institute_id,
                'branch_id' => $exam->branch_id,
                'online_exam_id' => $exam->id,
                'student_id' => $studentId,
                'user_id' => $userId,
                'attempt_no' => $attemptCount + 1,
                'answers' => [],
                'status' => OnlineExamAttempt::STATUS_STARTED,
                'grading_status' => OnlineExamAttempt::GRADING_NONE,
                'started_at' => now(),
                'expires_at' => $expiresAt,
            ]);

            $this->ensureQuestionsAssigned($attempt, $exam);

            return $attempt->fresh();
        });
    }

    /**
     * Deal the attempt its questions if it has none yet (new attempts, and
     * attempts started before per-attempt assignment existed).
     */
    public function ensureQuestionsAssigned(OnlineExamAttempt $attempt, ?OnlineExam $exam = null): void
    {
        if ($attempt->attemptQuestions()->exists()) {
            return;
        }

        $exam ??= $attempt->onlineExam;
        $drawn = $this->drawQuestions($exam);

        if ($drawn->isEmpty()) {
            throw new OnlineExamException('This online exam has no questions yet.');
        }

        $maxScore = 0.0;
        foreach ($drawn->values() as $index => $question) {
            OnlineExamAttemptQuestion::create([
                'institute_id' => $attempt->institute_id,
                'branch_id' => $attempt->branch_id,
                'attempt_id' => $attempt->id,
                'online_exam_question_id' => $question->id,
                'display_order' => $index + 1,
                'option_order' => $this->optionOrder($exam, $question),
                'max_marks' => (float) $question->marks,
            ]);
            $maxScore += (float) $question->marks;
        }

        $attempt->update(['max_score' => round($maxScore, 2)]);
    }

    /**
     * Pools deal `draw_count` random questions each; unpooled questions are all
     * included, or a random `questions_per_attempt` of them when set.
     */
    public function drawQuestions(OnlineExam $exam): Collection
    {
        $active = $exam->questions()->where('is_active', true)->get();
        $pools = $exam->pools()->get()->keyBy('id');

        $selected = collect();
        foreach ($active->whereNotNull('pool_id')->groupBy('pool_id') as $poolId => $poolQuestions) {
            $pool = $pools->get($poolId);
            $count = $pool ? min((int) $pool->draw_count, $poolQuestions->count()) : $poolQuestions->count();
            if ($count > 0) {
                $selected = $selected->merge($poolQuestions->shuffle()->take($count));
            }
        }

        $unpooled = $active->whereNull('pool_id');
        $perAttempt = (int) $exam->questions_per_attempt;
        $selected = $selected->merge(
            $perAttempt > 0 && $perAttempt < $unpooled->count()
                ? $unpooled->shuffle()->take($perAttempt)
                : $unpooled
        );

        return $exam->random_questions
            ? $selected->shuffle()->values()
            : $selected->sortBy(fn ($q) => [$q->display_order, $q->id])->values();
    }

    /** Number of questions and total marks a student will actually be dealt. */
    public function paperShape(OnlineExam $exam): array
    {
        $active = $exam->questions()->where('is_active', true)->get();
        $count = 0;
        $marks = 0.0;

        foreach ($exam->pools()->get() as $pool) {
            $inPool = $active->where('pool_id', $pool->id);
            $take = min((int) $pool->draw_count, $inPool->count());
            $count += $take;
            // Marks can differ within a pool; report the average dealt.
            $marks += $inPool->count() ? $take * ((float) $inPool->avg('marks')) : 0;
        }

        $unpooled = $active->whereNull('pool_id');
        $perAttempt = (int) $exam->questions_per_attempt;
        if ($perAttempt > 0 && $perAttempt < $unpooled->count()) {
            $count += $perAttempt;
            $marks += $perAttempt * (float) $unpooled->avg('marks');
        } else {
            $count += $unpooled->count();
            $marks += (float) $unpooled->sum('marks');
        }

        return ['questions' => $count, 'marks' => round($marks, 2)];
    }

    private function optionOrder(OnlineExam $exam, OnlineExamQuestion $question): ?array
    {
        $options = $question->options ?? [];
        if (! is_array($options) || $options === []) {
            return null;
        }

        $keys = array_keys($options);
        // Statement order of a multi-true/false question is part of its answer key.
        if ($exam->random_options && $question->type !== OnlineExamQuestion::TYPE_MULTIPLE_TRUE_FALSE) {
            shuffle($keys);
        }

        return $keys;
    }

    /**
     * The attempt's questions, in the order and with the option order that was
     * dealt. Never includes answer keys.
     */
    public function questionsForAttempt(OnlineExamAttempt $attempt): Collection
    {
        $this->ensureQuestionsAssigned($attempt);
        $saved = $this->answerMap($attempt->answers ?? []);

        return $attempt->attemptQuestions()->with('question')->get()
            ->filter(fn (OnlineExamAttemptQuestion $row) => $row->question)
            ->map(function (OnlineExamAttemptQuestion $row) use ($saved) {
                $question = $row->question;
                $options = $question->options ?? [];
                $ordered = [];
                if (is_array($options) && $options !== []) {
                    $isList = array_is_list($options);
                    foreach ($row->option_order ?? array_keys($options) as $key) {
                        if (array_key_exists($key, $options)) {
                            $isList ? $ordered[] = $options[$key] : $ordered[$key] = $options[$key];
                        }
                    }
                }

                return [
                    'id' => $question->id,
                    'attempt_question_id' => $row->id,
                    'number' => $row->display_order,
                    'type' => $question->type,
                    'question_text' => $question->question_text,
                    'image' => $question->image,
                    'options' => $ordered,
                    'marks' => (float) $row->max_marks,
                    'difficulty' => $question->difficulty,
                    'saved_answer' => $saved[$question->id] ?? null,
                ];
            })->values();
    }

    /**
     * @throws OnlineExamException
     */
    public function saveAnswers(OnlineExamAttempt $attempt, array $answers): OnlineExamAttempt
    {
        if ($attempt->isSubmitted()) {
            throw new OnlineExamException('This attempt has already been submitted.');
        }

        if ($attempt->isExpired()) {
            return $this->finalizeAttempt($attempt, $answers ?: ($attempt->answers ?? []), true);
        }

        $attempt->update(['answers' => $this->normaliseAnswers($attempt, $answers)]);

        return $attempt;
    }

    /**
     * @throws OnlineExamException
     */
    public function submit(OnlineExamAttempt $attempt, array $answers, bool $auto = false): OnlineExamAttempt
    {
        if ($attempt->isSubmitted()) {
            throw new OnlineExamException('This attempt has already been submitted.');
        }

        // After expiry only what was saved before the deadline counts.
        if ($attempt->isExpired()) {
            return $this->finalizeAttempt($attempt, $attempt->answers ?? [], true);
        }

        return $this->finalizeAttempt($attempt, $answers, $auto);
    }

    /**
     * Auto-submit every running attempt whose time is up. Runs from the
     * scheduler without an authenticated user, so tenant scopes are not applied
     * and each attempt carries its own institute/branch.
     */
    public function autoSubmitExpired(): int
    {
        $count = 0;
        OnlineExamAttempt::withoutGlobalScopes()
            ->where('status', OnlineExamAttempt::STATUS_STARTED)
            ->where('expires_at', '<=', now())
            ->orderBy('id')
            ->chunkById(100, function ($attempts) use (&$count) {
                foreach ($attempts as $attempt) {
                    $this->finalizeAttempt($attempt, $attempt->answers ?? [], true);
                    $count++;
                }
            });

        return $count;
    }

    /** Close an exam and submit everybody still writing it. */
    public function closeExam(OnlineExam $exam): int
    {
        $exam->update(['status' => OnlineExam::STATUS_CLOSED]);

        $running = OnlineExamAttempt::where('online_exam_id', $exam->id)
            ->where('status', OnlineExamAttempt::STATUS_STARTED)
            ->get();

        foreach ($running as $attempt) {
            $this->finalizeAttempt($attempt, $attempt->answers ?? [], true);
        }

        return $running->count();
    }

    /**
     * Record teacher marks for descriptive / short answers.
     *
     * @param  array<int, array{attempt_question_id: int, marks: float|int|string, feedback?: ?string}>  $grades
     *
     * @throws OnlineExamException
     */
    public function grade(OnlineExamAttempt $attempt, array $grades, ?int $graderId): OnlineExamAttempt
    {
        if (! $attempt->isSubmitted()) {
            throw new OnlineExamException('Only a submitted attempt can be graded.');
        }

        return DB::transaction(function () use ($attempt, $grades, $graderId) {
            $rows = $attempt->attemptQuestions()->with('question')->get()->keyBy('id');

            foreach ($grades as $grade) {
                $row = $rows->get((int) ($grade['attempt_question_id'] ?? 0));
                if (! $row || ! $row->question) {
                    throw new OnlineExamException('A graded question does not belong to this attempt.');
                }
                if ($row->question->isAutoScorable()) {
                    throw new OnlineExamException('Objective questions are marked automatically.');
                }

                $marks = (float) $grade['marks'];
                if ($marks < 0 || $marks > (float) $row->max_marks) {
                    throw new OnlineExamException("Marks must be between 0 and {$row->max_marks}.");
                }

                $row->update([
                    'awarded_marks' => $marks,
                    'is_correct' => $marks >= (float) $row->max_marks,
                    'needs_grading' => false,
                    'graded_by' => $graderId,
                    'graded_at' => now(),
                    'feedback' => $grade['feedback'] ?? $row->feedback,
                ]);
            }

            return $this->recalculate($attempt->fresh());
        });
    }

    /**
     * The attempt that counts for a student under the exam's score policy;
     * only final (submitted and fully graded) attempts are considered.
     */
    public function countedAttempt(OnlineExam $exam, int $studentId): ?OnlineExamAttempt
    {
        $attempts = OnlineExamAttempt::where('online_exam_id', $exam->id)
            ->where('student_id', $studentId)
            ->get();

        return $this->pickCounted($attempts, (string) $exam->score_policy);
    }

    /**
     * Apply a score policy to one student's attempts (already loaded).
     */
    public function pickCounted(Collection $attempts, string $policy): ?OnlineExamAttempt
    {
        $final = $attempts->filter(fn (OnlineExamAttempt $a) => $a->isFinal())->sortBy('id')->values();

        return match ($policy) {
            OnlineExam::SCORE_BEST => $final->sortByDesc(fn ($a) => [(float) $a->score, -$a->id])->first(),
            OnlineExam::SCORE_FIRST => $final->first(),
            default => $final->last(),
        };
    }

    private function finalizeAttempt(OnlineExamAttempt $attempt, array $answers, bool $auto): OnlineExamAttempt
    {
        return DB::transaction(function () use ($attempt, $answers, $auto) {
            // Lock the row: the scheduler and the student's own submit can race.
            $locked = OnlineExamAttempt::withoutGlobalScopes()->whereKey($attempt->id)->lockForUpdate()->first();
            if (! $locked || $locked->isSubmitted()) {
                return $locked ?? $attempt;
            }

            $exam = OnlineExam::withoutGlobalScopes()->find($locked->online_exam_id);
            try {
                $this->ensureQuestionsAssigned($locked, $exam);
            } catch (OnlineExamException) {
                // An exam emptied of questions still lets the attempt close (score 0).
            }

            $answers = $this->normaliseAnswers($locked, $answers);
            $map = $this->answerMap($answers);

            $autoScore = 0.0;
            $correct = 0;
            $incorrect = 0;
            $skipped = 0;
            $pending = false;

            $rows = OnlineExamAttemptQuestion::withoutGlobalScopes()
                ->where('attempt_id', $locked->id)
                ->with(['question' => fn ($q) => $q->withoutGlobalScopes()])
                ->get();

            foreach ($rows as $row) {
                $question = $row->question;
                if (! $question) {
                    continue;
                }

                $given = $map[$question->id] ?? null;
                $update = ['answer' => $given === null ? null : (array) $given];

                if ($this->isBlank($given)) {
                    $skipped++;
                    $update += ['awarded_marks' => 0, 'is_correct' => false, 'needs_grading' => false];
                } elseif (! $question->isAutoScorable()) {
                    $pending = true;
                    $update += ['awarded_marks' => null, 'is_correct' => null, 'needs_grading' => true];
                } else {
                    $fraction = $this->correctFraction($question, $given);
                    $awarded = round((float) $row->max_marks * $fraction, 2);
                    $autoScore += $awarded;

                    if ($fraction >= 1.0) {
                        $correct++;
                    } else {
                        $incorrect++;
                        if ($fraction <= 0.0 && $exam->negative_marking) {
                            $autoScore -= (float) $exam->negative_marks_per_wrong;
                        }
                    }
                    $update += ['awarded_marks' => $awarded, 'is_correct' => $fraction >= 1.0, 'needs_grading' => false];
                }

                $row->update($update);
            }

            $submittedAt = now();
            $locked->update([
                'answers' => $answers,
                'auto_score' => round($autoScore, 2),
                'correct_count' => $correct,
                'incorrect_count' => $incorrect,
                'skipped_count' => $skipped,
                'status' => $auto ? OnlineExamAttempt::STATUS_AUTO_SUBMITTED : OnlineExamAttempt::STATUS_SUBMITTED,
                'grading_status' => $pending ? OnlineExamAttempt::GRADING_PENDING : OnlineExamAttempt::GRADING_NONE,
                'submitted_at' => $submittedAt,
                'time_taken' => $locked->started_at ? max(0, (int) $locked->started_at->diffInSeconds($submittedAt)) : 0,
            ]);

            return $this->recalculate($locked->fresh(), $exam);
        });
    }

    /** Recompute score / pass / conversion from the per-question rows. */
    private function recalculate(OnlineExamAttempt $attempt, ?OnlineExam $exam = null): OnlineExamAttempt
    {
        $exam ??= OnlineExam::withoutGlobalScopes()->find($attempt->online_exam_id);

        $rows = OnlineExamAttemptQuestion::withoutGlobalScopes()
            ->where('attempt_id', $attempt->id)
            ->with(['question' => fn ($q) => $q->withoutGlobalScopes()])
            ->get();

        $manual = $rows->filter(fn ($row) => $row->question && ! $row->question->isAutoScorable());
        $pending = $manual->contains(fn ($row) => $row->needs_grading);
        $manualScore = (float) $manual->sum(fn ($row) => (float) $row->awarded_marks);

        $score = max(0, round((float) $attempt->auto_score + $manualScore, 2));
        $maxScore = (float) ($attempt->max_score ?: $rows->sum(fn ($row) => (float) $row->max_marks));

        $attempt->update([
            'score' => $score,
            'converted_score' => $this->convertScore(
                $score,
                (float) ($exam->online_full_marks ?: $maxScore ?: $exam->total_marks),
                (float) ($exam->academic_full_marks ?: $maxScore ?: $exam->total_marks),
                (bool) $exam->conversion_enabled
            ),
            'grading_status' => $pending
                ? OnlineExamAttempt::GRADING_PENDING
                : ($manual->isNotEmpty() && $manual->contains(fn ($row) => $row->graded_at) ? OnlineExamAttempt::GRADING_GRADED : $attempt->grading_status),
            'is_passed' => ! $pending && $score >= (float) $exam->pass_marks,
        ]);

        return $attempt->fresh();
    }

    /**
     * Fraction (0..1) of the question's marks earned by an answer.
     */
    public function correctFraction(OnlineExamQuestion $question, mixed $given): float
    {
        $correct = $question->correct_answer ?? [];
        $correct = is_array($correct) ? array_values($correct) : [$correct];
        $givenList = is_array($given) ? array_values($given) : [$given];

        switch ($question->type) {
            case OnlineExamQuestion::TYPE_FILL_BLANK:
                // Any one of the accepted answers is correct.
                $answer = $this->normalise($givenList[0] ?? '');

                return in_array($answer, array_map(fn ($v) => $this->normalise($v), $correct), true) ? 1.0 : 0.0;

            case OnlineExamQuestion::TYPE_MULTIPLE_TRUE_FALSE:
                // One true/false per statement, partial credit per statement.
                if ($correct === []) {
                    return 0.0;
                }
                $right = 0;
                foreach ($correct as $i => $expected) {
                    if (array_key_exists($i, $givenList) && $this->normalise($givenList[$i]) === $this->normalise($expected)) {
                        $right++;
                    }
                }

                return $right / count($correct);

            default:
                $a = array_map(fn ($v) => $this->normalise($v), $correct);
                $b = array_map(fn ($v) => $this->normalise($v), $givenList);
                sort($a);
                sort($b);

                return $a === $b ? 1.0 : 0.0;
        }
    }

    private function normalise(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return mb_strtolower(preg_replace('/\s+/u', ' ', trim((string) $value)));
    }

    private function isBlank(mixed $given): bool
    {
        if ($given === null || $given === '' || $given === []) {
            return true;
        }

        return is_array($given) && array_filter($given, fn ($v) => $v !== null && $v !== '') === [];
    }

    /**
     * Keep only answers to questions actually dealt to this attempt, in the
     * canonical [{question_id, selected_options|answer}] shape.
     */
    private function normaliseAnswers(OnlineExamAttempt $attempt, array $answers): array
    {
        $allowed = OnlineExamAttemptQuestion::withoutGlobalScopes()
            ->where('attempt_id', $attempt->id)
            ->pluck('online_exam_question_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $out = [];
        foreach ($this->answerMap($answers) as $questionId => $value) {
            if (! in_array((int) $questionId, $allowed, true)) {
                continue;
            }
            $out[] = is_array($value)
                ? ['question_id' => (int) $questionId, 'selected_options' => array_values($value)]
                : ['question_id' => (int) $questionId, 'answer' => $value];
        }

        return $out;
    }

    /**
     * Accepts either [{question_id, selected_options|answer}] or [question_id => value].
     *
     * @return array<int, mixed>
     */
    private function answerMap(array $answers): array
    {
        $map = [];
        foreach ($answers as $key => $answer) {
            if (is_array($answer) && array_key_exists('question_id', $answer)) {
                $map[(int) $answer['question_id']] = $answer['selected_options'] ?? $answer['answer'] ?? null;
            } elseif (is_int($key) || ctype_digit((string) $key)) {
                $map[(int) $key] = $answer;
            }
        }

        return $map;
    }
}
