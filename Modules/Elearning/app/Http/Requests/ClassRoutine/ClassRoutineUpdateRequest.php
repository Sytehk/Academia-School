<?php

namespace Modules\Elearning\Http\Requests\ClassRoutine;

use Illuminate\Foundation\Http\FormRequest;

class ClassRoutineUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'course_id' => 'required|integer|exists:courses,id',
            'teacher_id' => 'required|integer|exists:users,id',
            'room_id' => 'required|integer|exists:rooms,id',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'status' => 'nullable|boolean',
            'days' => 'required|array|min:1',
            'days.*' => 'integer|exists:days,id',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
