<?php

namespace Modules\Academic\Http\Requests\OptionalSubjectConfig;

use Illuminate\Foundation\Http\FormRequest;

class OptionalSubjectConfigUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'class_id' => 'required|exists:classes,id',
            'group_id' => 'nullable|exists:student_groups,id',
            'name' => 'required|string|max:100',
            'total_mark' => 'required|integer|min:1',
            'selection_limit' => 'required|integer|min:1',
            'is_active' => 'nullable|boolean',
            'subjects' => 'required|array|min:1',
            'subjects.*.subject_id' => 'required|exists:subjects,id',
            'subjects.*.mark' => 'required|integer|min:1',
            'subjects.*.priority' => 'nullable|integer',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
