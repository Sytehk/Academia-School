<?php

namespace Modules\Transport\Http\Requests\BusStop;

use Illuminate\Foundation\Http\FormRequest;

class BusStopStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'route_id' => 'required|integer|exists:bus_routes,id',
            'stop_name' => 'required|string|max:191',

            // GPS VALIDATION (UPDATED)
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',

            'stop_order' => 'nullable|integer|min:0',
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
