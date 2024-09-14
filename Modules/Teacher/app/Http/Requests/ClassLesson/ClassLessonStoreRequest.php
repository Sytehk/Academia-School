<?php

namespace Modules\Teacher\Http\Requests\ClassLesson;

use Illuminate\Foundation\Http\FormRequest;

class ClassLessonStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:191',
            'note' => 'nullable|string',
            'date' => 'required|date',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'nullable',
            'file' => 'required|mimes:doc,pdf,docx,zip',
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
