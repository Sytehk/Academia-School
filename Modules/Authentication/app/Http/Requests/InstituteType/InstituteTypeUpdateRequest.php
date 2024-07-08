<?php

namespace Modules\Authentication\Http\Requests\InstituteType;

use Illuminate\Foundation\Http\FormRequest;

class InstituteTypeUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'], // 2MB
            'status' => ['required', 'in:0,1'], // or boolean
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
