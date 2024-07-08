<?php

namespace Modules\Authentication\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:50|unique:users',
            'phone' => 'required|string|max:25|unique:users', // |regex:/^\d{10,15}$/
            'password' => 'required|string|min:6|confirmed',
            'role_id' => 'required|numeric',
            'linkedin' => 'nullable|string|max:255',
            'google_plus' => 'nullable|string|max:255',
            'facebook' => 'nullable|string|max:255',
            'twitter' => 'nullable|string|max:255',
            'image' => 'nullable|image|max:5120',
        ];
    }

    public function message(): array
    {
        return [
            //
        ];
    }
}
