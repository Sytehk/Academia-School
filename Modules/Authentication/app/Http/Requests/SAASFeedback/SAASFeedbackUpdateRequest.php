<?php

namespace Modules\Authentication\Http\Requests\SAASFeedback;

use Illuminate\Foundation\Http\FormRequest;

class SAASFeedbackUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string'],
            'thumbnail_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'video_url' => ['nullable', 'string', 'max:500'],
            'note' => ['nullable', 'string', 'max:1000'],
            'ratting' => ['nullable'],
            'status' => ['nullable'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
