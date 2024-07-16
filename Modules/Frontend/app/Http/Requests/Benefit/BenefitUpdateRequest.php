<?php

namespace Modules\Frontend\Http\Requests\Benefit;

use Illuminate\Foundation\Http\FormRequest;

class BenefitUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status' => 'nullable|in:0,1',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
