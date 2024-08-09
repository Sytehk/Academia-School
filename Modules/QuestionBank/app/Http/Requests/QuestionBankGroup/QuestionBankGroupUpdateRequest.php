<?php

namespace Modules\QuestionBank\Http\Requests\QuestionBankGroup;

use Illuminate\Foundation\Http\FormRequest;

class QuestionBankGroupUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'class_id' => 'required|tenant_exists:question_bank_classes,id',
            'name' => 'required|string|max:255',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
