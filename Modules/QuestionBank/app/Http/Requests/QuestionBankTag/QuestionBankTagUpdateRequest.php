<?php

namespace Modules\QuestionBank\Http\Requests\QuestionBankTag;

use Illuminate\Foundation\Http\FormRequest;

class QuestionBankTagUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'tag_name' => 'required|string|max:255',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
