<?php

namespace Modules\Elearning\Http\Requests\Day;

use Illuminate\Foundation\Http\FormRequest;

class DayStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'serial' => ['nullable', 'integer'],
            'status' => ['nullable', 'boolean'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
