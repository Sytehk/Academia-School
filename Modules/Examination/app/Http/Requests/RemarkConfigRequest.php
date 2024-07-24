<?php

namespace Modules\Examination\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RemarkConfigRequest extends FormRequest
{
    public function rules()
    {
        return [
            'remark_title' => 'required|string|max:255', // Title should be a string and required, with a max length of 255
            'remarks' => 'required|string', // Remarks should be a required string
        ];
    }

    public function messages()
    {
        return [
            'remark_title.required' => 'The remark title is required.',
            'remark_title.string' => 'The remark title must be a string.',
            'remark_title.max' => 'The remark title may not be greater than 255 characters.',
            'remarks.required' => 'Remarks are required.',
            'remarks.string' => 'Remarks must be a string.',
        ];
    }
}
