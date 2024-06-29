<?php

namespace Modules\Academic\Http\Requests\GoogleMeetMeeting;

use Illuminate\Foundation\Http\FormRequest;

class GoogleMeetMeetingUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'teacher_id' => 'nullable|integer',
            'class_id' => 'nullable|integer',
            'section_id' => 'nullable|integer',
            'group_id' => 'nullable|integer',
            'subject_id' => 'nullable|integer',
            'start_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'nullable',
            'duration' => 'required|integer|min:1',
            'notes' => 'nullable|string',
            'visibility' => 'nullable|array',
        ];
    }
}
