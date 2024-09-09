<?php

namespace Modules\Transport\Http\Requests\BusRoute;

use Illuminate\Foundation\Http\FormRequest;

class BusRouteUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'route_name' => 'required|string|max:191',
            'bus_id' => 'nullable|integer|exists:buses,id',
            'start_location' => 'required|string|max:191',
            'end_location' => 'required|string|max:191',
            'total_distance' => 'nullable|string',
            'estimated_time' => 'nullable|string',
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
