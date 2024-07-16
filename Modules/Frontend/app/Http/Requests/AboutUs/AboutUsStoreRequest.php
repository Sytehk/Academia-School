<?php

namespace Modules\Frontend\Http\Requests\AboutUs;

use Illuminate\Foundation\Http\FormRequest;

class AboutUsStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:191',
            'description' => 'nullable|string',
            'long_description' => 'nullable|string',
            'image' => 'nullable|image',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('description')) {
            $this->merge(['description' => sanitize_rich_text($this->description)]);
        }

        if ($this->has('long_description')) {
            $this->merge(['long_description' => sanitize_rich_text($this->long_description)]);
        }
    }
}
