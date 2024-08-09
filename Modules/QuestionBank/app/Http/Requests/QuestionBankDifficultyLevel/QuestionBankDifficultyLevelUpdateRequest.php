<?php

namespace Modules\QuestionBank\Http\Requests\QuestionBankDifficultyLevel;

use Illuminate\Foundation\Http\FormRequest;

class QuestionBankDifficultyLevelUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'level_name' => 'required|string|max:255',
            'level_code' => 'nullable|string|max:255',
            'status' => 'nullable|string',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
