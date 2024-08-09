<?php

namespace Modules\QuestionBank\Http\Requests\Quiz;

use Illuminate\Foundation\Http\FormRequest;

class QuizUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Relations
            'created_by' => ['nullable', 'exists:users,id'],

            // General
            'title' => ['required', 'string', 'max:255'],
            'description' => 'nullable',
            'guidelines' => 'nullable',
            'show_description_on_course_page' => ['boolean'],
            'type' => ['nullable', 'in:practice,mock,quick_test,exam'],

            // Questions - OPTIONAL
            'question_ids' => ['nullable', 'array'], // Can be updated later
            'question_ids.*' => ['integer', 'tenant_exists:questions,id'],

            // Timing
            'start_time' => ['nullable'],
            'end_time' => ['nullable'],
            'has_time_limit' => ['sometimes', 'boolean'],
            'time_limit_value' => ['nullable', 'integer', 'min:1'],
            'time_limit_unit' => ['nullable', 'in:seconds,minutes,hours,days,weeks'],
            'on_expiry' => ['nullable', 'in:auto_submit,prevent_submit,grace_time'],

            // Grading
            'marks_per_question' => ['nullable', 'numeric', 'min:0'],
            'negative_marks_per_wrong_answer' => ['nullable', 'numeric', 'min:0'],
            'pass_mark' => ['nullable', 'numeric', 'min:0'],
            'attempts_allowed' => ['nullable', 'integer', 'min:0'],
            'result_visibility' => ['nullable', 'in:immediate,after_review,never'],

            // Layout
            'layout_pages' => ['nullable', 'integer', 'min:1'],
            'shuffle_questions' => ['boolean'],
            'shuffle_options' => ['boolean'],

            // Security
            'access_type' => ['nullable', 'in:none,password,public'],
            'access_password' => ['nullable', 'string'],

            // Status
            'status' => ['nullable', 'in:active,inactive,draft'],
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
