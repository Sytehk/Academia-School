<?php

namespace Modules\Elearning\Http\Requests\Content;

use Illuminate\Foundation\Http\FormRequest;

class ContentUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // validation rules
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
