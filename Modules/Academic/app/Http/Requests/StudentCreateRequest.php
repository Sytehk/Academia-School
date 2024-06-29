<?php

namespace Modules\Academic\Http\Requests;

use App\Services\AcademicContextService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\Authentication\Models\Institute;

class StudentCreateRequest extends FormRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $instituteId = get_institute_id();
        $branchId = get_branch_id();
        $autoAdmissionNo = Institute::find($instituteId)?->admission_no_mode === 'auto';

        return [
            'first_name' => 'required|string|max:50',
            'last_name' => 'nullable|string|max:50',
            'father_name' => 'nullable|string|max:50',
            'mother_name' => 'nullable|string|max:50',
            'class_id' => [
                'required',
                Rule::exists('classes', 'id')->where(fn ($q) => $q->where('institute_id', $instituteId)->where('branch_id', $branchId)),
            ],
            'section_id' => [
                'required',
                Rule::exists('sections', 'id')->where(fn ($q) => $q->where('institute_id', $instituteId)->where('branch_id', $branchId)),
            ],
            'group' => [
                'nullable',
                Rule::exists('student_groups', 'id')->where(fn ($q) => $q->where('institute_id', $instituteId)->where('branch_id', $branchId)),
            ],
            // REGISTRATION NO UNIQUE — skipped in auto mode, where whatever the
            // caller sends is replaced by AdmissionNumberService anyway, so it
            // must never fail validation for colliding with someone else's number.
            'registration_no' => array_filter([
                'nullable',
                'string',
                'max:50',
                $autoAdmissionNo ? null : Rule::unique('students', 'registration_no')
                    ->where(fn ($query) => $query->where('institute_id', $instituteId)),
            ]),
            // ROLL UNIQUE (student_sessions OR students table based on your structure)
            'roll' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('student_sessions', 'roll')->where(function ($query) use ($instituteId) {
                    return $query->where('institute_id', $instituteId)
                        ->where('session_id', get_option('academic_year'))
                        ->where('class_id', request()->class_id)
                        ->where('section_id', request()->section_id);
                }),
            ],
            'blood_group' => 'nullable|string|max:4',
            'religion' => 'nullable|string|max:20',
            'gender' => 'nullable|string|in:Male,Female,Other',
            'address' => 'nullable|string',
            // EMAIL VALIDATION (users table)
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->where(function ($query) use ($instituteId) {
                    return $query->where('institute_id', $instituteId);
                }),
            ],
            // PHONE VALIDATION (users table + students table)
            'phone' => [
                'required',
                'string',
                'regex:/^[0-9+\-\s()]{7,20}$/',
                Rule::unique('users', 'phone')->where(function ($query) use ($instituteId) {
                    return $query->where('institute_id', $instituteId);
                }),
                Rule::unique('students', 'phone')->where(function ($query) use ($instituteId) {
                    return $query->where('institute_id', $instituteId);
                }),
            ],

            'password' => 'required|string|min:6|confirmed',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
            'birthday' => 'nullable|date|before:today',
            'state' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:100',
            'activities' => 'nullable|string|max:50',
            'remarks' => 'nullable|string|max:50',
            'information_sent_to_name' => 'nullable|string|max:30|required_with:information_sent_to_email,information_sent_to_phone',
            'information_sent_to_email' => 'nullable|string|email|max:100|required_with:information_sent_to_name,information_sent_to_phone',
            'information_sent_to_phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s()]{7,20}$/', 'required_with:information_sent_to_name,information_sent_to_email'],
            'information_sent_to_relation' => 'nullable|string|max:30',
            'information_sent_to_address' => 'nullable|string|max:100',
            'qr_code' => 'nullable|string|max:8',
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'This email already exists in this institute.',
            'phone.unique' => 'This phone number already exists.',
            'phone.regex' => 'Please enter a valid phone number.',
            'roll.unique' => 'This roll is already assigned in this class/section.',
            'registration_no.unique' => 'Registration number already exists.',
            'class_id.exists' => 'The selected class is invalid.',
            'section_id.exists' => 'The selected section is invalid.',
            'group.exists' => 'The selected group is invalid.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            try {
                app(AcademicContextService::class)->assertClassSectionGroup(
                    (int) $this->input('class_id'),
                    (int) $this->input('section_id'),
                    $this->filled('group') ? (int) $this->input('group') : null,
                );
            } catch (ValidationException $e) {
                foreach ($e->errors() as $field => $messages) {
                    foreach ($messages as $message) {
                        $validator->errors()->add($field, $message);
                    }
                }
            }
        });
    }
}
