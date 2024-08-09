<?php

namespace Modules\QuestionBank\Http\Requests\QuestionBankTopic;

use Illuminate\Foundation\Http\FormRequest;

class QuestionBankTopicStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'question_bank_chapter_id' => 'nullable|tenant_exists:question_bank_chapters,id',
            'name' => 'required|string|max:255',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
