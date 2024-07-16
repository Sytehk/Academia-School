<?php

namespace Modules\Frontend\Http\Requests\AdmissionForm;

use Illuminate\Foundation\Http\FormRequest;

class AdmissionFormUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // Academic
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'group_id' => 'nullable|exists:student_groups,id',

            // Student
            'first_name' => 'required|string|max:50',
            'last_name' => 'nullable|string|max:50',

            'father_name' => 'nullable|string|max:100',
            'mother_name' => 'nullable|string|max:100',

            'email' => ['nullable', 'email'],

            'phone' => ['required'],

            'birthday' => 'nullable|date',

            'gender' => 'nullable|string|max:20',
            'blood_group' => 'nullable|string|max:10',
            'religion' => 'nullable|string|max:30',

            'address' => 'nullable|string',

            // Admission
            'registration_no' => ['nullable'],

            'previous_school' => 'nullable|string|max:255',
            'previous_class' => 'nullable|string|max:100',

            // Guardian
            'guardian_name' => 'nullable|string|max:100',
            'guardian_email' => 'nullable|email|max:100',
            'guardian_phone' => 'nullable|string|max:30',
            'guardian_relation' => 'nullable|string|max:50',
            'guardian_address' => 'nullable|string',

            // Others
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
            'remarks' => 'nullable|string|max:500',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
