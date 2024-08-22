<?php

namespace Modules\Finance\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class FeesCreateRequest extends FormRequest
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
            'class_id' => 'required|exists:classes,id',
            'group_id' => 'nullable|exists:student_groups,id',
            'student_category_id' => 'nullable|exists:student_categories,id',
            'fee_head_id' => 'required|exists:fee_heads,id',
            'fee_amount' => 'required|numeric|min:0',
            'fine_amount' => 'nullable|numeric|min:0',
            'fund_id' => 'nullable|exists:accounting_funds,id',
        ];
    }
}
