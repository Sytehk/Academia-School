<?php

namespace Modules\Transport\Http\Requests\Driver;

use Illuminate\Foundation\Http\FormRequest;

class DriverStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:191',
            'phone_number' => 'required|string|max:191',
            'license_number' => 'required|numeric',
            'assigned_bus_id' => 'nullable|integer|exists:buses,id',
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
