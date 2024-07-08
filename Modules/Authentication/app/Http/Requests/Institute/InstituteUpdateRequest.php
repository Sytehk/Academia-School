<?php

namespace Modules\Authentication\Http\Requests\Institute;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InstituteUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $instituteId = $this->route('institute');

        return [

            'name' => [
                'required',
                'string',
                'max:100',
            ],

            'email' => [
                'nullable',
                'email',
                'max:100',
                Rule::unique('institutes', 'email')->ignore($instituteId),
            ],

            'phone' => [
                'nullable',
                'string',
                'max:15',
                Rule::unique('institutes', 'phone')->ignore($instituteId),
            ],

            'domain' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('institutes', 'domain')->ignore($instituteId),
            ],

            'address' => [
                'nullable',
                'string',
                'max:255',
            ],

            'institute_type' => [
                'nullable',
                'string',
                'max:100',
            ],

            'theme_id' => [
                'nullable',
                'integer',
                'exists:themes,id',
            ],

            'status' => [
                'nullable',
                'boolean',
            ],

            'logo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            // Owner (created during onboarding) — optional edits
            'owner_name' => [
                'nullable',
                'string',
                'max:100',
            ],

            'owner_email' => [
                'nullable',
                'email',
                'max:100',
            ],

            'owner_phone' => [
                'nullable',
                'string',
                'max:20',
            ],

            'owner_password' => [
                'nullable',
                'string',
                'min:6',
                'confirmed',
            ],
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
