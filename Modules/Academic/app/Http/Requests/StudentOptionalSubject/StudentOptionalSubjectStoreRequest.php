<?php

namespace Modules\Academic\Http\Requests\StudentOptionalSubject;

use Illuminate\Foundation\Http\FormRequest;

class StudentOptionalSubjectStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'student_session_id' => 'required|exists:student_sessions,id',
            'optional_subject_config_id' => 'required|exists:optional_subject_configs,id',
            'subject_id' => 'required|exists:subjects,id',
            'mark' => 'required|integer|min:1',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
