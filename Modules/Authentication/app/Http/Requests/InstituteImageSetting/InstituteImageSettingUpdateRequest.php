<?php

namespace Modules\Authentication\Http\Requests\InstituteImageSetting;

use Illuminate\Foundation\Http\FormRequest;

class InstituteImageSettingUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'header_logo_light_theme' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'header_logo_dark_theme' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'footer_logo_light_theme' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'footer_logo_dark_theme' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'favicon' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
