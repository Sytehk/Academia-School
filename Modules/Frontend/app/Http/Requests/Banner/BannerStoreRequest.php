<?php

namespace Modules\Frontend\Http\Requests\Banner;

use Illuminate\Foundation\Http\FormRequest;

class BannerStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:191',
            'description' => 'nullable|string|max:255',
            'button_name' => 'nullable|string|max:20',
            'button_link' => 'nullable|string|max:100',
            'image' => 'required|image',
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
