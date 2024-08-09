<?php

namespace Modules\QuestionBank\Http\Requests\QuestionBankYear;

use Illuminate\Foundation\Http\FormRequest;

class QuestionBankYearStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'year' => 'required|string|max:255',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
