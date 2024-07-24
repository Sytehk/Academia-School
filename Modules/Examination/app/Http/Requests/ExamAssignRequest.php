<?php

namespace Modules\Examination\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExamAssignRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Set authorization logic if needed
    }

    public function rules()
    {
        return [
            'class_id' => 'required|exists:classes,id',
            'exam_ids' => 'required|array|min:1',
            'exam_ids.*' => 'required|exists:exams,id',
            'merit_process_type_id' => 'required|exists:merit_process_types,id',
        ];
    }

    public function messages()
    {
        return [
            'class_id.required' => 'Class is required.',
            'class_id.exists' => 'Selected class does not exist.',
            'exam_ids.required' => 'At least one exam must be selected.',
            'exam_ids.array' => 'Exam IDs must be an array.',
            'exam_ids.*.exists' => 'One or more selected exams are invalid.',
            'merit_process_type_id.required' => 'Merit process type is required.',
            'merit_process_type_id.exists' => 'Selected merit process type is invalid.',
        ];
    }
}
