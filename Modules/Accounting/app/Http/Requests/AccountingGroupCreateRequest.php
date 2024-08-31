<?php

namespace Modules\Accounting\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AccountingGroupCreateRequest extends FormRequest
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
        $commonRules = [
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('accounting_groups')
                    ->where('institute_id', get_institute_id())
                    ->where('branch_id', get_branch_id()),
            ],
            'accounting_category_id' => 'required|numeric|exists:accounting_categories,id',
            'code' => 'nullable|numeric',
        ];

        return $commonRules;
    }
}
