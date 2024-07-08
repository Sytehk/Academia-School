<?php

namespace Modules\Authentication\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:50',
            'phone' => 'required|string|max:25', // |regex:/^\d{10,15}$/
            'password' => 'nullable|string|min:6|confirmed',
            'user_type' => 'required',
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
