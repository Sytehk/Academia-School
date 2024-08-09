<?php

namespace Modules\QuestionBank\Http\Requests\QuestionBankLevel;

use Illuminate\Foundation\Http\FormRequest;

class QuestionBankLevelStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'level_name' => 'required|string|max:255',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
