<?php

namespace Modules\Teacher\Http\Requests\Gamification;

use Illuminate\Foundation\Http\FormRequest;

class GamificationUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'student_id' => ['required', 'exists:students,id'],
            'class_id' => ['required', 'exists:classes,id'],
            'section_id' => ['nullable', 'exists:sections,id'],
            'note' => ['nullable', 'string', 'max:255'],
            'file' => [
                'nullable',
                'file',
                'mimes:pdf,doc,docx,jpg,jpeg,png',
                'max:10240',
            ],
        ];
    }

    public function message(): array
    {
        return [
            //
        ];
    }
}
