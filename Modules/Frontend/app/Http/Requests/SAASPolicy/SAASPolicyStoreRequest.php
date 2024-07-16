<?php

namespace Modules\Frontend\Http\Requests\SAASPolicy;

use Illuminate\Foundation\Http\FormRequest;

class SAASPolicyStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'type' => 'required|in:privacy,cookie,terms',
            'description' => 'required|string',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
