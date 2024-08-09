<?php

namespace Modules\QuestionBank\Http\Requests\QuestionBankSubject;

use Illuminate\Foundation\Http\FormRequest;

class QuestionBankSubjectStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'question_category_id' => 'required|tenant_exists:question_categories,id',
            'class_id' => 'nullable|tenant_exists:question_bank_classes,id',
            'group_id' => 'nullable|tenant_exists:question_bank_groups,id',
            'color_code' => 'nullable|string|max:25',
            'code' => 'nullable|string|max:25',
            'type' => 'nullable|string|max:25',
            'priority' => 'nullable|integer|min:1',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Max 2MB
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Max 2MB
            'status' => 'nullable|in:0,1',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
