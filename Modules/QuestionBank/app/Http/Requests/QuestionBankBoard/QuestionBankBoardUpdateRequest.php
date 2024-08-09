<?php

namespace Modules\QuestionBank\Http\Requests\QuestionBankBoard;

use Illuminate\Foundation\Http\FormRequest;

class QuestionBankBoardUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'board' => 'required|string|max:255',
            'short_name' => 'required|string|max:255',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
