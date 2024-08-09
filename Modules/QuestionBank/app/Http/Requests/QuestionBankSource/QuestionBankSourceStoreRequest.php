<?php

namespace Modules\QuestionBank\Http\Requests\QuestionBankSource;

use Illuminate\Foundation\Http\FormRequest;

class QuestionBankSourceStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'source_name' => 'required|string|max:255',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
