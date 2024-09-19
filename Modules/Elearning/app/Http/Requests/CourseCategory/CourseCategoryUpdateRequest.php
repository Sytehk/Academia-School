<?php

namespace Modules\Elearning\Http\Requests\CourseCategory;

use Illuminate\Foundation\Http\FormRequest;

class CourseCategoryUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            // 'slug'             => 'nullable|string|max:255|regex:/^[a-z0-9-]+$/|unique:course_categories,slug',
            'image' => 'nullable|image',
            'bg_color' => 'nullable|string|max:20',
            'priority' => 'nullable|integer|min:1',
            'enable_homepage' => 'nullable|boolean',
            'description' => 'nullable|string|max:5000',
            'parent_id' => 'nullable|integer|exists:course_categories,id',
            'status' => 'nullable|in:0,1',
        ];
    }

    /**
     * Custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'priority.integer' => 'Priority must be a valid number.',
            'parent_id.exists' => 'The selected parent category does not exist.',
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
