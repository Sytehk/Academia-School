<?php

namespace Modules\QuestionBank\Http\Requests\QuestionBankClass;

use Illuminate\Foundation\Http\FormRequest;

class QuestionBankClassUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
