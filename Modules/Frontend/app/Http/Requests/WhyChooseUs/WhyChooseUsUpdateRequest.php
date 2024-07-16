<?php

namespace Modules\Frontend\Http\Requests\WhyChooseUs;

use Illuminate\Foundation\Http\FormRequest;

class WhyChooseUsUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:191',
            'description' => 'nullable|string|max:255',
            'icon' => 'required|image',
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
