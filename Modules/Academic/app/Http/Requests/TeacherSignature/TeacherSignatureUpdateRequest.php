<?php

namespace Modules\Academic\Http\Requests\TeacherSignature;

use Illuminate\Foundation\Http\FormRequest;

class TeacherSignatureUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'signature_image' => 'required|image|mimes:png,jpg,jpeg,webp|max:2048',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
