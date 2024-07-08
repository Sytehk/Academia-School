<?php

namespace Modules\Authentication\Http\Requests\Plan;

use Illuminate\Foundation\Http\FormRequest;

class PlanStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'student_limit' => 'required|integer|min:0',
            'branch_limit' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0|max:999999.99',
            'duration_days' => 'nullable|integer|min:1',
            'is_custom' => 'required|boolean',
            'is_free' => 'required|boolean',
            'status' => 'nullable|in:1,2',
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
