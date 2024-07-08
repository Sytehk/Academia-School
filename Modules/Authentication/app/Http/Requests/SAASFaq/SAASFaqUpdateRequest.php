<?php

namespace Modules\Authentication\Http\Requests\SAASFaq;

use Illuminate\Foundation\Http\FormRequest;

class SAASFaqUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'question' => 'required|string|max:255',
            'answer' => 'required|string',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
