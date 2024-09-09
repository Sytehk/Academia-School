<?php

namespace Modules\Transport\Http\Requests\Bus;

use Illuminate\Foundation\Http\FormRequest;

class BusStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'bus_number' => 'required|string|max:191|unique:buses,bus_number',
            'model' => 'nullable|string|max:191',
            'capacity' => 'required|numeric',
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
