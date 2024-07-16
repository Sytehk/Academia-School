<?php

namespace Modules\Frontend\Http\Requests\MobileAppSection;

use Illuminate\Foundation\Http\FormRequest;

class MobileAppSectionStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'heading' => 'nullable|string',
            'string' => 'nullable|string',
            'feature_one' => 'nullable|string',
            'feature_two' => 'nullable|string',
            'feature_three' => 'nullable|string',
            'play_store_link' => 'nullable|string',
            'app_store_link' => 'nullable|string',
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
