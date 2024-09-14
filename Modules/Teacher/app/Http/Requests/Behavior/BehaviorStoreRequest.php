<?php

namespace Modules\Teacher\Http\Requests\Behavior;

use Illuminate\Foundation\Http\FormRequest;

class BehaviorStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'date' => ['nullable', 'date'],
            'student_id' => ['required', 'integer'],
            'class_id' => ['required', 'integer'],
            'section_id' => ['nullable', 'integer'],
            'note' => ['nullable', 'string', 'max:255'],
            'file' => [
                'nullable',
                'file',
                'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,webp',
                'max:10240', // 10MB
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
