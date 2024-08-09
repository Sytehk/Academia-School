<?php

namespace Modules\QuestionBank\Http\Requests\QuestionBankType;

use Illuminate\Foundation\Http\FormRequest;

class QuestionBankTypeUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'type_name' => 'required|string|max:255',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
