<?php

namespace Modules\Academic\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Academic\Models\Staff;

class StaffUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // You can adjust the authorization logic as needed
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
        $staffId = optional($this->route('staff'))->id ?? (int) $this->route('staff');
        $staff = Staff::find($staffId);
        $userId = $staff?->user_id;

        return [
            'name' => 'required|string|max:50',
            'phone' => [
                'required',
                'string',
                'regex:/^[0-9+\-\s()]{7,20}$/',
                Rule::unique('users', 'phone')->where(fn ($q) => $q->where('institute_id', $instituteId))->ignore($userId),
            ],
            'designation' => 'nullable|string|max:50',
            'birthday' => 'nullable|date|before:today',
            'gender' => 'nullable|string|max:50',
            'religion' => 'nullable|string|max:50',
            'address' => 'nullable|string',
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
            'email' => [
                'nullable',
                'email',
                'max:50',
                Rule::unique('users', 'email')->where(fn ($q) => $q->where('institute_id', $instituteId))->ignore($userId),
            ],
            'password' => 'nullable|min:6|confirmed',
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
