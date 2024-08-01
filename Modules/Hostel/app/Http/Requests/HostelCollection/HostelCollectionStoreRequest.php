<?php

namespace Modules\Hostel\Http\Requests\HostelCollection;

use Illuminate\Foundation\Http\FormRequest;

class HostelCollectionStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'room_member_id' => 'required|integer|exists:room_members,id',
            'session_id' => 'required|integer',
            'month' => 'nullable|string',
            'fee_heads' => 'required|array',
            'fee_heads.*' => 'nullable|numeric|min:0',
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
