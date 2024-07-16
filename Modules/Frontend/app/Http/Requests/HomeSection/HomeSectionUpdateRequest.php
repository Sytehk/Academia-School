<?php

namespace Modules\Frontend\Http\Requests\HomeSection;

use Illuminate\Foundation\Http\FormRequest;

class HomeSectionUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'serial' => 'nullable|integer|min:0',
            'status' => 'nullable|integer|in:1,2',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
