<?php

namespace Modules\Elearning\Http\Requests\Chapter;

use Illuminate\Foundation\Http\FormRequest;

class ChapterUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'course_id' => 'required|integer|exists:courses,id',
            'title' => 'required|string|max:255',
            'image' => 'nullable|image',
            'description' => 'nullable|string|max:10000',
            'meta_description' => 'nullable|string|max:5000',
            'meta_keywords' => 'nullable|string|max:5000',
            'status' => 'nullable|string|in:active,inactive,draft',
        ];
    }

    /**
     * Custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            //
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
