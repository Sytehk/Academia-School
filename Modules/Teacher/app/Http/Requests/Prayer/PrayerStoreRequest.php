<?php

namespace Modules\Teacher\Http\Requests\Prayer;

use Illuminate\Foundation\Http\FormRequest;

class PrayerStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:191',
            'date' => 'required|date',
            'note' => 'required|string',
            'file' => 'nullable|mimes:doc,pdf,docx,zip',
        ];
    }

    public function message(): array
    {
        return [
            //
        ];
    }
}
