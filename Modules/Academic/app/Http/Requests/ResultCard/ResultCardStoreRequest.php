<?php

namespace Modules\Academic\Http\Requests\ResultCard;

use Illuminate\Foundation\Http\FormRequest;

class ResultCardStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'qr_code' => 'nullable|string|max:255',
            'signature_id' => 'nullable|exists:signatures,id',
            'teacher_signature_id' => 'nullable|exists:signatures,id',
            'background_image' => 'nullable|image|mimes:png,jpg,jpeg,webp',
            'header_logo' => 'nullable|image|mimes:png,jpg,jpeg,webp',
            'stamp_image' => 'nullable|image|mimes:png,jpg,jpeg,webp',
            'border_design' => 'nullable|image|mimes:png,jpg,jpeg,webp',
            'watermark' => 'nullable|image|mimes:png,jpg,jpeg,webp',
            'status' => 'nullable|in:1,0',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
