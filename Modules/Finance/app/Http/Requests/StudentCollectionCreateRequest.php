<?php

namespace Modules\Finance\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StudentCollectionCreateRequest extends FormRequest
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
            'student_id' => 'required|exists:students,id',
            'total_paid' => 'required|numeric|min:0',
            'total_payable' => 'required|numeric|min:0',
            'date' => 'required|date',
            'ledger_id' => 'required|exists:accounting_ledgers,id',
            'sub_head_ids' => 'nullable|array',
            'sub_head_ids.*' => 'nullable|exists:sub_heads,id',
        ];
    }
}
