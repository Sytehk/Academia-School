<?php

namespace Modules\Authentication\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            Rule::unique('roles')->where(function ($query) {
                return $query->where('institute_id', get_institute_id())
                    ->where('branch_id', get_branch_id());
            }),
            'description' => 'nullable|string|max:255',
            'permissions' => 'required|array',
        ];
    }

    public function message(): array
    {
        return [
            //
        ];
    }
}
