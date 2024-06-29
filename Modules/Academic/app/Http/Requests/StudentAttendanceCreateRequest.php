<?php

namespace Modules\Academic\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StudentAttendanceCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'student_ids' => 'required|array',
            'attendance' => 'required|array',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'period_id' => 'nullable|exists:periods,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'date' => 'required',
        ];
    }
}
