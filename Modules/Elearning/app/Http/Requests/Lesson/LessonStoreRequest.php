<?php

namespace Modules\Elearning\Http\Requests\Lesson;

use Illuminate\Foundation\Http\FormRequest;

class LessonStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'course_id' => 'required|string|exists:courses,id',
            'chapter_id' => 'required|string|exists:chapters,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable',
            'thumbnail_image' => 'nullable',
            'video_type' => 'nullable|string',
            'video_url' => 'nullable|string|max:1000',
            'embedded_url' => 'nullable|string|max:1000',
            'uploaded_video_path' => 'nullable|file|max:51200', // 50MB max
            'document_file' => 'nullable|file|max:2024', // 50MB max
            'playback_hours' => 'nullable|string|min:0|max:10',
            'playback_minutes' => 'nullable|string|min:0|max:59',
            'playback_seconds' => 'nullable|string|min:0|max:59',
            'is_scheduled' => 'nullable|boolean',
            'scheduled_at' => 'nullable|date',
            'visibility' => 'nullable|in:none,password,public',
            'password' => 'nullable|required_if:visibility,password|string|min:4|max:255',
            'attachments.*' => 'file', // 10MB max
            'attachments' => 'nullable|array',
            'status' => 'nullable|in:active,inactive,draft',
            'meta_description' => 'nullable|string|max:5000',
            'meta_keywords' => 'nullable|string|max:5000',
        ];
    }

    /**
     * Custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            //
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
