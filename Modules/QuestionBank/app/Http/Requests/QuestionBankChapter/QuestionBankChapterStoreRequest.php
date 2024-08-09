<?php

namespace Modules\QuestionBank\Http\Requests\QuestionBankChapter;

use Illuminate\Foundation\Http\FormRequest;

class QuestionBankChapterStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'subject_id' => 'required|tenant_exists:question_bank_subjects,id',
            'chapter_no' => 'nullable|string|max:25',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
