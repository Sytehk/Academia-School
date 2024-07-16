<?php

namespace Modules\Frontend\Http\Requests\Policy;

use Illuminate\Foundation\Http\FormRequest;

class PolicyUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'description' => 'required|string',
            'status' => 'nullable|integer|in:1,2',
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
