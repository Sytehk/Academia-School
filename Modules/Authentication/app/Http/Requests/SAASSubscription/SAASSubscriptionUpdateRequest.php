<?php

namespace Modules\Authentication\Http\Requests\SAASSubscription;

use Illuminate\Foundation\Http\FormRequest;

class SAASSubscriptionUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'email' => [
                'nullable',
                'string',
                'email:rfc,dns',
                'max:100',
            ],
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
