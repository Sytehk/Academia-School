<?php

namespace Modules\Authentication\Http\Requests\SAASBenefit;

use Illuminate\Foundation\Http\FormRequest;

class SAASBenefitStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'heading' => [
                'required',
                'string',
                'max:255',
            ],

            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'para_title_one' => [
                'nullable',
                'string',
                'max:100',
            ],

            'para_title_two' => [
                'nullable',
                'string',
                'max:100',
            ],

            'para_title_three' => [
                'nullable',
                'string',
                'max:100',
            ],

            'para_title_four' => [
                'nullable',
                'string',
                'max:100',
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp,svg',
                'max:2048',
            ],

            'status' => [
                'nullable',
                'integer',
                'in:0,1',
            ],

            'created_by' => [
                'nullable',
                'exists:users,id',
            ],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
