<?php

namespace Modules\Frontend\Http\Requests\Achievement;

use Illuminate\Foundation\Http\FormRequest;

class AchievementStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'graduates' => 'nullable|numeric|min:0',
            'certified_teachers' => 'nullable|numeric|min:0',
            'student_campus' => 'nullable|numeric|min:0',
            'students' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:0,1',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
