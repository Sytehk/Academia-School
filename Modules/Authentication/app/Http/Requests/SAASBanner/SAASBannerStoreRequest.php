<?php

namespace Modules\Authentication\Http\Requests\SAASBanner;

use Illuminate\Foundation\Http\FormRequest;

class SAASBannerStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'button_name' => 'nullable|string|max:100',
            'button_link' => 'nullable|string|max:500',
            'button_two_name' => 'nullable|string|max:100',
            'button_two_link' => 'nullable|string|max:500',
            'image' => 'nullable|image',
            'status' => 'nullable|integer|in:0,1',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
