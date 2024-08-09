<?php

namespace Modules\QuestionBank\Http\Requests\QuestionBankTest;

use Illuminate\Foundation\Http\FormRequest;

class QuestionBankTestUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'test_name' => 'required|string|max:255',
            'test_id' => 'nullable|string|max:255',
            'image' => 'nullable',
            'duration' => 'nullable',
            'start_date' => 'nullable',
            'end_date' => 'nullable',
            'status' => 'nullable|string',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
