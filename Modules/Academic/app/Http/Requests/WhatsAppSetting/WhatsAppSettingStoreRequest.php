<?php

namespace Modules\Academic\Http\Requests\WhatsAppSetting;

use Illuminate\Foundation\Http\FormRequest;

class WhatsAppSettingStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // validation rules
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
