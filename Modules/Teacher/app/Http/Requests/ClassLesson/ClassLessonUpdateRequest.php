<?php

namespace Modules\Teacher\Http\Requests\ClassLesson;

use Illuminate\Foundation\Http\FormRequest;

class ClassLessonUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'nullable|string|max:191',
            'note' => 'nullable|string',
            'date' => 'nullable|date',
            'class_id' => 'nullable',
            'section_id' => 'nullable',
            'subject_id' => 'nullable',
            'file' => 'nullable|mimes:doc,pdf,docx,zip',
            'file_2' => 'nullable|mimes:doc,pdf,docx,zip',
            'file_3' => 'nullable|mimes:doc,pdf,docx,zip',
            'file_4' => 'nullable|mimes:doc,pdf,docx,zip',
        ];
    }

    public function message(): array
    {
        return [
            //
        ];
    }
}
