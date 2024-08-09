<?php

namespace Modules\QuestionBank\Http\Requests\QuizTopic;

use Illuminate\Foundation\Http\FormRequest;

class QuizTopicStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'time_limit' => 'nullable|integer|min:1|max:180',
            'quiz_type' => 'required|in:practice,mock,quick_test',
            'topics' => 'required|array|min:1',
            'topics.*.question_bank_subject_id' => 'nullable|integer',
            'topics.*.question_bank_chapter_id' => 'nullable|integer',
            'topics.*.question_category_id' => 'nullable|integer',
            'topics.*.limit' => 'required|integer|min:1|max:50',

            // Custom rule: each topic must have at least a subject or chapter
            'topics.*' => function ($attribute, $value, $fail) {
                if (empty($value['question_bank_subject_id']) && empty($value['question_bank_chapter_id'])) {
                    $fail('Each topic must have at least a subject or chapter.');
                }
            },
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
