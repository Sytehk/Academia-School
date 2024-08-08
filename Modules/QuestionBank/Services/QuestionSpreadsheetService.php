<?php

namespace Modules\QuestionBank\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\QuestionBank\Models\Question;

/**
 * Question bank import / export in one spreadsheet format (CSV or XLSX):
 *
 *   type, question, option_1 … option_6, correct_answer, marks, negative_marks,
 *   explanation, question_bank_subject_id, question_bank_chapter_id,
 *   question_bank_difficulty_level_id, question_category_id, status
 *
 * `correct_answer` holds the correct option text(s) separated by "|"; for a
 * fill-in-the-blank question, every accepted answer; for multiple_true_false,
 * one true/false per statement in order. An exported file imports unchanged.
 */
class QuestionSpreadsheetService
{
    public const TYPES = [
        'multiple_choice', 'multiple_select', 'true_false', 'multiple_true_false',
        'fill_blank', 'short_answer', 'descriptive',
    ];

    public const MAX_OPTIONS = 6;

    public const MAX_ROWS = 2000;

    /** @return array<int, string> */
    public function headings(): array
    {
        $options = array_map(fn ($i) => 'option_'.$i, range(1, self::MAX_OPTIONS));

        return array_merge(
            ['id', 'type', 'question'],
            $options,
            ['correct_answer', 'marks', 'negative_marks', 'explanation',
                'question_bank_subject_id', 'question_bank_chapter_id',
                'question_bank_difficulty_level_id', 'question_category_id', 'status'],
        );
    }

    /**
     * @return Collection<int, array<int, mixed>>
     */
    public function exportRows(array $filters = []): Collection
    {
        $query = Question::query()->orderBy('id');
        foreach (['question_bank_subject_id', 'question_bank_chapter_id', 'question_category_id', 'question_bank_difficulty_level_id', 'type', 'status'] as $column) {
            if (! empty($filters[$column])) {
                $query->where($column, $filters[$column]);
            }
        }

        return $query->get()->map(function (Question $q) {
            $options = array_values((array) ($q->options ?? []));
            $row = [$q->id, $q->type, $this->plainText($q->question)];
            for ($i = 0; $i < self::MAX_OPTIONS; $i++) {
                $row[] = $options[$i] ?? '';
            }

            return array_merge($row, [
                implode('|', array_map('strval', (array) ($q->correct_answer ?? []))),
                $q->marks,
                $q->negative_marks,
                $this->plainText($q->explanation),
                $q->question_bank_subject_id,
                $q->question_bank_chapter_id,
                $q->question_bank_difficulty_level_id,
                $q->question_category_id,
                $q->status,
            ]);
        });
    }

    /**
     * Validate every row, then insert them all or none.
     *
     * @param  array<int, array<string, mixed>>  $rows  heading-keyed rows
     * @return array{imported: int}
     *
     * @throws ValidationException
     */
    public function import(array $rows): array
    {
        $rows = array_values(array_filter($rows, fn ($row) => trim((string) ($row['question'] ?? '')) !== ''));
        if ($rows === []) {
            throw ValidationException::withMessages(['file' => ['The file contains no questions.']]);
        }
        if (count($rows) > self::MAX_ROWS) {
            throw ValidationException::withMessages(['file' => ['At most '.self::MAX_ROWS.' questions can be imported at once.']]);
        }

        $errors = [];
        $prepared = [];
        foreach ($rows as $index => $row) {
            $line = $index + 2;
            $validator = Validator::make($row, [
                'type' => ['required', Rule::in(self::TYPES)],
                'question' => 'required|string|max:20000',
                'marks' => 'nullable|numeric|min:0|max:1000',
                'negative_marks' => 'nullable|numeric|min:0|max:1000',
                'explanation' => 'nullable|string|max:5000',
                'question_bank_subject_id' => 'nullable|integer|tenant_exists:question_bank_subjects,id',
                'question_bank_chapter_id' => 'nullable|integer|tenant_exists:question_bank_chapters,id',
                'question_bank_difficulty_level_id' => 'nullable|integer|tenant_exists:question_bank_difficulty_levels,id',
                'question_category_id' => 'nullable|integer|tenant_exists:question_categories,id',
                'status' => ['nullable', Rule::in(['active', 'inactive', 'draft'])],
            ]);
            if ($validator->fails()) {
                $errors["row_{$line}"] = $validator->errors()->all();

                continue;
            }

            $options = [];
            for ($i = 1; $i <= self::MAX_OPTIONS; $i++) {
                $value = trim((string) ($row['option_'.$i] ?? ''));
                if ($value !== '') {
                    $options[] = $value;
                }
            }
            $correct = array_values(array_filter(
                array_map('trim', explode('|', (string) ($row['correct_answer'] ?? ''))),
                fn ($v) => $v !== ''
            ));

            if ($problem = $this->answerKeyProblem((string) $row['type'], $options, $correct)) {
                $errors["row_{$line}"] = [$problem];

                continue;
            }

            $prepared[] = [
                'type' => $row['type'],
                'question' => trim((string) $row['question']),
                'options' => $options ?: null,
                // NOT NULL column: descriptive / short answers store an empty key.
                'correct_answer' => $correct,
                'marks' => (int) round((float) ($row['marks'] ?? 1)) ?: 1,
                'negative_marks' => (int) round((float) ($row['negative_marks'] ?? 0)),
                'explanation' => $row['explanation'] ?? null,
                'question_bank_subject_id' => $row['question_bank_subject_id'] ?: null,
                'question_bank_chapter_id' => $row['question_bank_chapter_id'] ?: null,
                'question_bank_difficulty_level_id' => $row['question_bank_difficulty_level_id'] ?: null,
                'question_category_id' => $row['question_category_id'] ?: null,
                'status' => $row['status'] ?: 'active',
            ];
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        DB::transaction(function () use ($prepared) {
            foreach ($prepared as $data) {
                Question::create($data + [
                    'institute_id' => get_institute_id(),
                    'created_by' => auth()->id(),
                ]);
            }
        });

        return ['imported' => count($prepared)];
    }

    private function answerKeyProblem(string $type, array $options, array $correct): ?string
    {
        return match ($type) {
            'multiple_choice', 'true_false' => count($options) < 2 || count($correct) !== 1 || ! in_array($correct[0], $options, true)
                ? 'Needs at least two options and exactly one correct_answer matching an option.' : null,
            'multiple_select' => count($options) < 2 || $correct === [] || array_diff($correct, $options) !== []
                ? 'Needs options and correct answers that match them.' : null,
            'multiple_true_false' => $options === [] || count($correct) !== count($options)
                ? 'Give one true/false in correct_answer for every statement.' : null,
            'fill_blank' => $correct === [] ? 'Needs at least one accepted answer in correct_answer.' : null,
            default => null,
        };
    }

    /** Bank text may be a Quill delta (JSON ops) or plain/HTML text. */
    private function plainText(mixed $value): string
    {
        if ($value === null) {
            return '';
        }
        $decoded = is_string($value) ? json_decode($value, true) : $value;
        if (is_array($decoded)) {
            $ops = $decoded['ops'] ?? $decoded;

            return trim(collect($ops)->map(fn ($op) => is_array($op) ? ($op['insert'] ?? '') : '')->filter(fn ($s) => is_string($s))->implode(''));
        }

        return (string) $value;
    }
}
