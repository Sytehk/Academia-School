<?php

namespace Modules\Academic\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StaffCreateRequest extends FormRequest
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

        return [
            'name' => 'required|string|max:50',
            'phone' => [
                'required',
                'string',
                'regex:/^[0-9+\-\s()]{7,20}$/',
                Rule::unique('users', 'phone')->where(fn ($q) => $q->where('institute_id', $instituteId)),
            ],
            'password' => 'required|string|min:6|confirmed',
            'designation' => 'nullable|string|max:50',
            'birthday' => 'nullable|date|before:today',
            'gender' => 'nullable|string|max:50',
            'religion' => 'nullable|string|max:50',
            'sl' => 'nullable|integer',
            'role_id' => [
                'required',
                'integer',
                Rule::exists('roles', 'id')->where(fn ($q) => $q->where('institute_id', $instituteId)->where('branch_id', $branchId)),
            ],
            'department_id' => [
                'nullable',
                Rule::exists('departments', 'id')->where(fn ($q) => $q->where('institute_id', $instituteId)->where('branch_id', $branchId)),
            ],
            'blood' => 'nullable|string',
            'address' => 'nullable|string',
            'email' => [
                'nullable',
                'email',
                'max:50',
                Rule::unique('users', 'email')->where(fn ($q) => $q->where('institute_id', $instituteId)),
            ],
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'phone.unique' => 'This phone number already exists.',
            'phone.regex' => 'Please enter a valid phone number.',
            'email.unique' => 'This email already exists in this institute.',
            'role_id.exists' => 'The selected role is invalid.',
            'department_id.exists' => 'The selected department is invalid.',
        ];
    }
}
