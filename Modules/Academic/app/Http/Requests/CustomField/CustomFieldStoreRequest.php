<?php

namespace Modules\Academic\Http\Requests\CustomField;

use Illuminate\Foundation\Http\FormRequest;

class CustomFieldStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // 'module' => 'nullable|string|max:50',
            'field_name' => ['required', 'max:100'],
            'label' => 'required|string|max:255',
            // 'field_type' => 'required|in:text,number,date,select,checkbox,textarea',
            // 'options' => 'nullable|array',
            // 'options.*' => 'string|max:255',
            'is_required' => 'nullable|boolean',
            'show_in_form' => 'nullable|boolean',
            'show_in_list' => 'nullable|boolean',
            'show_in_profile' => 'nullable|boolean',
            'serial' => 'nullable',
            'status' => 'nullable|in:0,1',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
