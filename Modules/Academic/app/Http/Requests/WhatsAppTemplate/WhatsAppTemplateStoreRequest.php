<?php

namespace Modules\Academic\Http\Requests\WhatsAppTemplate;

use Illuminate\Foundation\Http\FormRequest;

class WhatsAppTemplateStoreRequest extends FormRequest
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
