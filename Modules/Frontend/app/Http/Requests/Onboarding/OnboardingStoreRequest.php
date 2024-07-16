<?php

namespace Modules\Frontend\Http\Requests\Onboarding;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OnboardingStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * Uniqueness is enforced against the institutes/users tables — the tables the
     * sign-up actually writes to. This previously had no uniqueness rules at all;
     * the controller instead scanned the onboardings JSON column with a raw
     * JSON_EXTRACT query, which could not use an index and never checked the real
     * tables, so a sign-up could collide with an existing institute.
     *
     * Soft-deleted rows are excluded: institutes and users are soft-deletable, and
     * a deleted record must not permanently reserve an email, phone or domain.
     */
    public function rules(): array
    {
        return [
            'institute_name' => ['required', 'string', 'max:100'],
            'institute_email' => [
                'required', 'email', 'max:255',
                Rule::unique('institutes', 'email')->whereNull('deleted_at'),
            ],
            'institute_phone' => [
                'required', 'string', 'max:15',
                Rule::unique('institutes', 'phone')->whereNull('deleted_at'),
            ],
            'institute_domain' => [
                'nullable', 'string', 'max:100',
                Rule::unique('institutes', 'domain')->whereNull('deleted_at'),
            ],
            'institute_type' => ['nullable', 'string', 'max:100'],
            'institute_address' => ['nullable', 'string', 'max:255'],
            'institute_logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'user_name' => ['required', 'string', 'max:100'],
            'user_email' => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email')->whereNull('deleted_at'),
            ],
            'user_phone' => ['required', 'string', 'max:50'],
            'password' => ['required', 'confirmed', 'string', 'min:6'],
            'user_avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'institute_email.unique' => _lang('An institute is already registered with this email address.'),
            'institute_phone.unique' => _lang('An institute is already registered with this phone number.'),
            'institute_domain.unique' => _lang('This domain is already taken.'),
            'user_email.unique' => _lang('An account already exists with this email address.'),
            'password.confirmed' => _lang('The password confirmation does not match.'),
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
