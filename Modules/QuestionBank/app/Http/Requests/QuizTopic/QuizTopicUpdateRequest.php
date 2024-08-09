<?php

namespace Modules\QuestionBank\Http\Requests\QuizTopic;

use Illuminate\Foundation\Http\FormRequest;

class QuizTopicUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'time_limit' => 'nullable|integer|min:1',
            'topics' => 'required|array|min:1',
            'topics.*.subject_id' => 'nullable|integer',
            'topics.*.chapter_id' => 'nullable|integer',
            'topics.*.question_category_id' => 'nullable|integer',
            'topics.*.limit' => 'required|integer|min:1',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
