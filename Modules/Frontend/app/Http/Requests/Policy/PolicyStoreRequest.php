<?php

namespace Modules\Frontend\Http\Requests\Policy;

use Illuminate\Foundation\Http\FormRequest;

class PolicyStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'type' => 'required|integer|in:1,2,3,4,5',
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
