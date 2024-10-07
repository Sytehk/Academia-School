<?php

namespace Modules\Student\Http\Requests\Enrollment;

use Illuminate\Foundation\Http\FormRequest;

class EnrollmentStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'course_id' => 'required|exists:courses,id', // Ensure the course exists
            // 'user_id' => 'required|exists:users,id', // Ensure the student exists
            'enrollment_type' => 'required|in:one_time,monthly_subscription',
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
