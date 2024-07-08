<?php

namespace Modules\Authentication\Http\Requests\SAASSubscription;

use Illuminate\Foundation\Http\FormRequest;

class SAASSubscriptionStoreRequest extends FormRequest
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
                'unique:s_a_a_s_subscriptions,email', // Ensure unique email for new s_a_a_s_subscriptions
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
