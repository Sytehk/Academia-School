<?php

namespace Modules\Accounting\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AccountTransactionCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'payment_method_id' => 'required|numeric',
            'fund_id' => 'required|numeric',
            'transaction_date' => 'required',
            'type' => 'required|string',
            'reference' => 'nullable|string',
            'description' => 'nullable|string',
        ];
    }
}
