<?php

namespace Modules\Academic\Http\Requests\CustomFieldValue;

use Illuminate\Foundation\Http\FormRequest;

class CustomFieldValueUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'model_type' => 'required|string',
            'model_id' => 'required|integer',
            'fields' => 'required|array',
            'fields.*.custom_field_id' => 'required|exists:custom_fields,id',
            'fields.*.value' => 'nullable|string|max:1000',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
