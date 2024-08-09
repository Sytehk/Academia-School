<?php

namespace Modules\QuestionBank\Http\Requests\Question;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QuestionStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'question' => 'required',
            'question_name' => 'nullable|string|max:255',
            'options' => 'required|array',
            'type' => ['required', Rule::in(['true_false', 'multiple_choice'])],
            'correct_answer' => 'required|string|in:option1,option2,option3,option4,option5,option6,true,false',
            'marks' => 'required|integer|min:1',
            'negative_marks' => 'nullable|integer|min:0',
            'status' => 'nullable|in:active,inactive,draft',
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
