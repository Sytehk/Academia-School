<?php

namespace Modules\Examination\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Examination\Exceptions\OnlineExamException;
use Modules\Examination\Models\OnlineExam;
use Modules\Examination\Models\OnlineExamAttempt;
use Modules\Examination\Models\OnlineExamQuestion;
use Modules\Examination\Models\OnlineExamQuestionPool;
use Modules\QuestionBank\Models\Question;

/**
 * Builds an online exam's paper: hand-written questions, questions pulled from
 * the institute's Question Bank (by id or by criteria), and random-draw pools.
 *
 * Bank questions are COPIED into the exam (question_id keeps the link): editing
 * or deleting a bank question later must never change a paper students have
 * already sat.
 */
class OnlineExamQuestionService
{
    public function __construct(
        private readonly OnlineExamScoringService $scoring,
    ) {}

    /** True once any student has been dealt this exam's questions. */
    public function isLocked(OnlineExam $exam): bool
    {
        return OnlineExamAttempt::where('online_exam_id', $exam->id)->exists();
    }

    /**
     * @throws OnlineExamException
     */
    public function assertEditable(OnlineExam $exam): void
    {
        if ($this->isLocked($exam)) {
            throw new OnlineExamException('Students have already attempted this exam; its questions can no longer be changed.');
        }
    }

    public function create(OnlineExam $exam, array $data): OnlineExamQuestion
    {
        $this->assertEditable($exam);
        $this->assertPool($exam, $data['pool_id'] ?? null);
        $this->assertAnswerKey($data['type'], $data['options'] ?? [], $data['correct_answer'] ?? []);

        $question = OnlineExamQuestion::create([
            'institute_id' => $exam->institute_id,
            'branch_id' => $exam->branch_id,
            'online_exam_id' => $exam->id,
            'pool_id' => $data['pool_id'] ?? null,
            'type' => $data['type'],
            'question_text' => $data['question_text'],
            'image' => $data['image'] ?? null,
            'options' => array_values($data['options'] ?? []) ?: null,
            'correct_answer' => array_values($data['correct_answer'] ?? []) ?: null,
            'explanation' => $data['explanation'] ?? null,
            'marks' => $data['marks'],
            'difficulty' => $data['difficulty'] ?? null,
            'display_order' => $this->nextOrder($exam),
            'is_active' => true,
        ]);

        $this->refreshTotals($exam);

        return $question;
    }

    public function update(OnlineExamQuestion $question, array $data): OnlineExamQuestion
    {
        $exam = $question->onlineExam;
        $this->assertEditable($exam);
        if (array_key_exists('pool_id', $data)) {
            $this->assertPool($exam, $data['pool_id']);
        }

        $type = $data['type'] ?? $question->type;
        $options = array_key_exists('options', $data) ? array_values($data['options'] ?? []) : ($question->options ?? []);
        $correct = array_key_exists('correct_answer', $data) ? array_values($data['correct_answer'] ?? []) : ($question->correct_answer ?? []);
        $this->assertAnswerKey($type, $options, $correct);

        $question->update(array_merge(
            array_intersect_key($data, array_flip(['question_text', 'image', 'explanation', 'marks', 'difficulty', 'display_order', 'is_active', 'pool_id'])),
            ['type' => $type, 'options' => $options ?: null, 'correct_answer' => $correct ?: null],
        ));

        $this->refreshTotals($exam);

        return $question->fresh();
    }

    public function delete(OnlineExamQuestion $question): void
    {
        $exam = $question->onlineExam;
        $this->assertEditable($exam);
        $question->delete();
        $this->refreshTotals($exam);
    }

    /**
     * Copy specific bank questions into the exam. Questions already in the exam
     * are skipped.
     *
     * @param  array<int, int>  $questionIds
     * @return array{added: int, skipped: int}
     */
    public function addFromBank(OnlineExam $exam, array $questionIds, ?int $poolId = null, ?float $marks = null): array
    {
        $this->assertEditable($exam);
        $this->assertPool($exam, $poolId);

        // Bank questions are institute-scoped (BelongsToInstitute), so an id
        // from another institute simply is not found.
        $bank = Question::whereIn('id', array_unique(array_map('intval', $questionIds)))->get();
        if ($bank->count() !== count(array_unique($questionIds))) {
            throw new OnlineExamException('One or more selected questions do not exist in your question bank.');
        }

        return $this->copyIn($exam, $bank, $poolId, $marks);
    }

    /**
     * Copy a random selection of bank questions matching the criteria.
     *
     * @param  array{question_bank_subject_id?: int, question_bank_chapter_id?: int, question_category_id?: int,
     *               question_bank_difficulty_level_id?: int, topic_ids?: array<int, int>, types?: array<int, string>}  $criteria
     * @return array{added: int, skipped: int, available: int}
     */
    public function addFromBankByCriteria(OnlineExam $exam, array $criteria, int $limit, ?int $poolId = null, ?float $marks = null): array
    {
        $this->assertEditable($exam);
        $this->assertPool($exam, $poolId);

        $alreadyIn = OnlineExamQuestion::where('online_exam_id', $exam->id)->whereNotNull('question_id')->pluck('question_id');

        $query = Question::query()
            ->where('status', 'active')
            ->whereNotIn('id', $alreadyIn->all() ?: [0]);

        foreach (['question_bank_subject_id', 'question_bank_chapter_id', 'question_category_id', 'question_bank_difficulty_level_id'] as $column) {
            if (! empty($criteria[$column])) {
                $query->where($column, (int) $criteria[$column]);
            }
        }
        if (! empty($criteria['topic_ids'])) {
            $query->whereHas('topics', fn ($q) => $q->whereIn('question_bank_topics.id', $criteria['topic_ids']));
        }
        if (! empty($criteria['types'])) {
            $query->whereIn('type', $criteria['types']);
        }

        $available = (clone $query)->count();
        $picked = $query->inRandomOrder()->limit($limit)->get();

        return $this->copyIn($exam, $picked, $poolId, $marks) + ['available' => $available];
    }

    public function createPool(OnlineExam $exam, string $name, int $drawCount): OnlineExamQuestionPool
    {
        $this->assertEditable($exam);

        $pool = OnlineExamQuestionPool::create([
            'institute_id' => $exam->institute_id,
            'branch_id' => $exam->branch_id,
            'online_exam_id' => $exam->id,
            'name' => $name,
            'draw_count' => $drawCount,
        ]);
        $this->refreshTotals($exam);

        return $pool;
    }

    public function updatePool(OnlineExamQuestionPool $pool, array $data): OnlineExamQuestionPool
    {
        $this->assertEditable($pool->onlineExam);
        $pool->update(array_intersect_key($data, array_flip(['name', 'draw_count'])));
        $this->refreshTotals($pool->onlineExam);

        return $pool->fresh();
    }

    public function deletePool(OnlineExamQuestionPool $pool): void
    {
        $exam = $pool->onlineExam;
        $this->assertEditable($exam);
        // Questions fall back to the unpooled set (FK is nullOnDelete).
        $pool->delete();
        $this->refreshTotals($exam);
    }

    /**
     * total_questions / total_marks describe the paper ONE student is dealt,
     * which is what pass marks and conversion are set against.
     */
    public function refreshTotals(OnlineExam $exam): void
    {
        $shape = $this->scoring->paperShape($exam);
        $exam->update(['total_questions' => $shape['questions'], 'total_marks' => $shape['marks']]);
    }

    /**
     * Readiness problems that block publishing, empty when publishable.
     *
     * @return array<int, string>
     */
    public function publishProblems(OnlineExam $exam): array
    {
        $problems = [];
        $active = $exam->questions()->where('is_active', true)->get();

        if ($active->isEmpty()) {
            $problems[] = 'The exam has no active questions.';
        }

        foreach ($exam->pools()->get() as $pool) {
            $inPool = $active->where('pool_id', $pool->id)->count();
            if ($inPool < $pool->draw_count) {
                $problems[] = "Pool \"{$pool->name}\" draws {$pool->draw_count} but has only {$inPool} active questions.";
            }
        }

        foreach ($active as $question) {
            if ($question->isAutoScorable() && empty($question->correct_answer)) {
                $problems[] = "Question #{$question->display_order} has no correct answer.";
            }
        }

        if ($exam->end_at && $exam->start_at && $exam->end_at->lte($exam->start_at)) {
            $problems[] = 'The end time must be after the start time.';
        }

        if ($exam->pass_marks > 0 && $exam->total_marks > 0 && (float) $exam->pass_marks > (float) $exam->total_marks) {
            $problems[] = 'Pass marks exceed the marks each student is dealt.';
        }

        return $problems;
    }

    private function copyIn(OnlineExam $exam, Collection $bank, ?int $poolId, ?float $marks): array
    {
        $alreadyIn = OnlineExamQuestion::where('online_exam_id', $exam->id)
            ->whereNotNull('question_id')
            ->pluck('question_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $added = 0;
        $skipped = 0;
        DB::transaction(function () use ($exam, $bank, $poolId, $marks, $alreadyIn, &$added, &$skipped) {
            $order = $this->nextOrder($exam);
            foreach ($bank as $question) {
                if (in_array((int) $question->id, $alreadyIn, true)) {
                    $skipped++;

                    continue;
                }

                OnlineExamQuestion::create([
                    'institute_id' => $exam->institute_id,
                    'branch_id' => $exam->branch_id,
                    'online_exam_id' => $exam->id,
                    'pool_id' => $poolId,
                    'question_id' => $question->id,
                    'type' => $question->type,
                    'question_text' => $this->bankText($question->question),
                    'image' => $question->image_file,
                    'options' => $this->asList($question->options) ?: null,
                    'correct_answer' => $this->asList($question->correct_answer) ?: null,
                    'explanation' => $this->bankText($question->explanation),
                    'marks' => $marks ?? max(0.0, (float) ($question->marks ?: 1)),
                    'difficulty' => $question->difficulty?->level_name,
                    'display_order' => $order++,
                    'is_active' => true,
                ]);
                $added++;
            }
        });

        $this->refreshTotals($exam);

        return ['added' => $added, 'skipped' => $skipped];
    }

    /** Bank text may be a Quill delta (JSON ops) or plain/HTML text. */
    private function bankText(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $decoded = is_string($value) ? json_decode($value, true) : $value;
        if (is_array($decoded)) {
            $ops = $decoded['ops'] ?? $decoded;

            return trim(collect($ops)->map(fn ($op) => is_array($op) ? ($op['insert'] ?? '') : '')->filter(fn ($s) => is_string($s))->implode(''));
        }

        return (string) $value;
    }

    private function asList(mixed $value): array
    {
        if (is_string($value)) {
            $value = json_decode($value, true) ?? [$value];
        }

        return is_array($value) ? array_values($value) : [];
    }

    private function nextOrder(OnlineExam $exam): int
    {
        return (int) OnlineExamQuestion::where('online_exam_id', $exam->id)->max('display_order') + 1;
    }

    /**
     * @throws OnlineExamException
     */
    private function assertPool(OnlineExam $exam, mixed $poolId): void
    {
        if ($poolId && ! OnlineExamQuestionPool::where('online_exam_id', $exam->id)->whereKey((int) $poolId)->exists()) {
            throw new OnlineExamException('The selected pool does not belong to this exam.');
        }
    }

    /**
     * @throws OnlineExamException
     */
    private function assertAnswerKey(string $type, array $options, array $correct): void
    {
        $options = array_map('strval', array_values($options));
        $correct = array_map('strval', array_values($correct));

        switch ($type) {
            case OnlineExamQuestion::TYPE_MULTIPLE_CHOICE:
            case OnlineExamQuestion::TYPE_TRUE_FALSE:
                if (count($options) < 2 || count($correct) !== 1 || ! in_array($correct[0], $options, true)) {
                    throw new OnlineExamException('A single-answer question needs at least two options and exactly one correct option.');
                }
                break;
            case OnlineExamQuestion::TYPE_MULTIPLE_SELECT:
                if (count($options) < 2 || $correct === [] || array_diff($correct, $options) !== []) {
                    throw new OnlineExamException('A multiple-select question needs options and correct answers chosen from them.');
                }
                break;
            case OnlineExamQuestion::TYPE_MULTIPLE_TRUE_FALSE:
                if ($options === [] || count($correct) !== count($options)) {
                    throw new OnlineExamException('Give one true/false answer for every statement.');
                }
                break;
            case OnlineExamQuestion::TYPE_FILL_BLANK:
                if ($correct === []) {
                    throw new OnlineExamException('A fill-in-the-blank question needs at least one accepted answer.');
                }
                break;
        }
    }
}
