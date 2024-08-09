<?php

namespace Modules\QuestionBank\Http\Requests\QuestionCategory;

use Illuminate\Foundation\Http\FormRequest;

class QuestionCategoryUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'color_code' => 'nullable|string|max:25',
            'priority' => 'nullable|integer|min:1',
            'status' => 'nullable|in:0,1',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Max 2MB
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Max 2MB
        ];
    }

    /**
     * Custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'priority.integer' => 'Priority must be a valid number.',
            'status.in' => 'Status must be either active or inactive.',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
