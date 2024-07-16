<?php

namespace Modules\Frontend\Http\Requests\AcademicImage;

use Illuminate\Foundation\Http\FormRequest;

class AcademicImageUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:191',
            'heading' => 'nullable|string|max:191',
            'description' => 'nullable|string',
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
    }
}
