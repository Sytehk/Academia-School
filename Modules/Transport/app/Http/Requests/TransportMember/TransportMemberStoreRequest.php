<?php

namespace Modules\Transport\Http\Requests\TransportMember;

use Illuminate\Foundation\Http\FormRequest;

class TransportMemberStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'student_id' => 'required|string|max:191',
            'assigned_route_id' => 'required|string|max:191',
            'assigned_stop_id' => 'required|numeric',
            'fare_amount' => 'required|string',
            'status' => 'nullable|string',
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
