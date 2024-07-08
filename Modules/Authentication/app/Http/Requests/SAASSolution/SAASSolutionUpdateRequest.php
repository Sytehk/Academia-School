<?php

namespace Modules\Authentication\Http\Requests\SAASSolution;

use Illuminate\Foundation\Http\FormRequest;

class SAASSolutionUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|image',
            'status' => 'nullable|integer|in:0,1',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
