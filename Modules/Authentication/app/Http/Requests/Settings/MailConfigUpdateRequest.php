<?php

namespace Modules\Authentication\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class MailConfigUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'MAIL_MAILER' => 'required|string|max:50',
            'MAIL_HOST' => 'required|string|max:255',
            'MAIL_PORT' => 'required|numeric',
            'MAIL_USERNAME' => 'nullable|string|max:255',
            'MAIL_PASSWORD' => 'nullable|string|max:255',
            'MAIL_FROM_ADDRESS' => 'required|email|max:255',
            'MAIL_FROM_NAME' => 'required|string|max:255',
        ];
    }

    public function message(): array
    {
        return [
            //
        ];
    }
}
