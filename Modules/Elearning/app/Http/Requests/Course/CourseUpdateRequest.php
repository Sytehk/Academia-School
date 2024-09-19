<?php

namespace Modules\Elearning\Http\Requests\Course;

use Illuminate\Foundation\Http\FormRequest;

class CourseUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'course_category_id' => 'nullable|string',
            'course_sub_category_id' => 'nullable|string',

            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255',

            'image' => 'nullable',
            'intro_video' => 'nullable|string|max:255',

            // Enums from migration
            'status' => 'required|string|in:draft,schedule,published,private',
            'publish_date' => 'nullable|date|after_or_equal:today',

            'type' => 'required|string|in:single,bundle',
            'payment_type' => 'required|string|in:free,recurring_payment,one_time',

            'invoice_title' => 'nullable|string|max:255',

            // Conditional pricing
            'regular_price' => 'required_if:payment_type,recurring_payment,one_time|nullable|numeric|min:0',
            'offer_price' => 'nullable|numeric|min:0|lte:regular_price',

            'repeat_count' => 'nullable|string|min:0',
            'fake_enrolled_students' => 'nullable|string|min:0',
            'total_classes' => 'nullable|string|min:0',
            'total_notes' => 'nullable|string|min:0',
            'total_exams' => 'nullable|string|min:0',

            'payment_duration' => 'nullable|string|max:255',
            'total_cycles' => 'nullable|string|min:1',

            // Booleans
            'is_infinity' => 'nullable|boolean',
            'is_auto_generate_invoice' => 'nullable|boolean',

            'description' => 'nullable',
            'class_routine_image' => 'nullable',

            // Video Settings
            'video_type' => 'nullable|in:none,youtube,vimeo,upload,external,embed',
            'video_url' => 'nullable|string|max:1000',
            'embedded_url' => 'nullable|string|max:1000',
            'uploaded_video_path' => 'nullable', // 50MB max

            'faqs' => 'nullable|json',

            // Optional extras (matching your table)
            'meta_description' => 'nullable|string|max:5000',
            'meta_keywords' => 'nullable|string|max:5000',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'requirements' => 'nullable|string|max:1000',
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
