<?php

namespace Modules\Authentication\Http\Requests\Currency;

use Illuminate\Foundation\Http\FormRequest;

class CurrencyStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'symbol' => ['required', 'string', 'max:10'],
            'code' => ['required', 'string', 'size:3', 'alpha', 'uppercase', 'unique:currencies,code'],
            'country_code' => ['nullable', 'string', 'size:2', 'alpha', 'uppercase'],
            'status' => ['required', 'in:1,2'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
