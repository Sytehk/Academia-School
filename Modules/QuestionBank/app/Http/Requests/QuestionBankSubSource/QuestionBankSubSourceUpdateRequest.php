<?php

namespace Modules\QuestionBank\Http\Requests\QuestionBankSubSource;

use Illuminate\Foundation\Http\FormRequest;

class QuestionBankSubSourceUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'sub_source_name' => 'required|string|max:255',
            'source_id' => 'required|tenant_exists:question_bank_sources,id',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
