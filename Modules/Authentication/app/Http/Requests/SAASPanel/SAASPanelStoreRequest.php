<?php

namespace Modules\Authentication\Http\Requests\SAASPanel;

use Illuminate\Foundation\Http\FormRequest;

class SAASPanelStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'button_name' => 'nullable|string',
            'button_link' => 'nullable|string',
            'icon' => 'nullable|image',
            'status' => 'nullable|integer|in:0,1',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
