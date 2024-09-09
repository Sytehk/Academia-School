<?php

namespace Modules\Transport\Http\Requests\TransportCollection;

use Illuminate\Foundation\Http\FormRequest;

class TransportCollectionStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'transport_member_id' => 'required|integer|exists:transport_members,id',
            'session_id' => 'required|integer',
            'month' => 'required|string',
            'fee_amount' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'fine' => 'nullable|numeric|min:0',
            'total_paid' => 'required|numeric|min:0',
            'ledger_id' => 'nullable|integer',
            'receive_ledger_id' => 'nullable|integer',
            'fund_id' => 'nullable|integer',
            'note' => 'nullable|string',
        ];
    }
}
