<?php

namespace Modules\Elearning\Http\Requests\CourseFaq;

use Illuminate\Foundation\Http\FormRequest;

class CourseFaqUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'course_id' => 'required|integer|exists:courses,id',
            'question' => 'required|string|max:255',
            'answer' => 'required|string|max:5000',
            'status' => 'nullable|string|in:0,1',
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
