<?php

namespace Modules\QuestionBank\Http\Requests\QuestionBankSession;

use Illuminate\Foundation\Http\FormRequest;

class QuestionBankSessionStoreRequest extends FormRequest
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
