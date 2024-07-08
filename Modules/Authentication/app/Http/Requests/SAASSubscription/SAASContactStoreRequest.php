<?php

namespace Modules\Authentication\Http\Requests\SAASSubscription;

use Illuminate\Foundation\Http\FormRequest;

class SAASContactStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:191',
            'email' => 'required|string|email|max:191',
            'phone' => 'nullable|string|max:25',
            'subject' => 'required|string',
            'message' => 'required|string|max:500',
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
