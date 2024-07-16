<?php

namespace Modules\Frontend\Http\Requests\ReadyToJoinUs;

use Illuminate\Foundation\Http\FormRequest;

class ReadyToJoinUsUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:191',
            'description' => 'nullable|string|max:255',
            'icon' => 'nullable|image',
            'button_name' => 'nullable|string',
            'button_link' => 'nullable|string',
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
