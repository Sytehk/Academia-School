<?php

namespace Modules\Elearning\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\Authenticatable;
use App\Traits\RequestSanitizerTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Elearning\Http\Requests\Course\CourseStoreRequest;
use Modules\Elearning\Http\Requests\Course\CourseUpdateRequest;
use Modules\Elearning\Models\Course;
use Modules\Elearning\Repositories\CourseRepository;

class CourseController extends Controller
{
    use Authenticatable, RequestSanitizerTrait;

    public function __construct(private CourseRepository $courseRepository) {}

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'course_category_id' => 'nullable|string|exists:course_categories,id',
            'course_sub_category_id' => 'nullable|string|exists:course_categories,id',
            'search' => 'nullable|string|max:255',
            'status' => 'nullable|in:draft,schedule,published,private',
            'type' => 'nullable|in:single,bundle',
            'sort_by' => 'nullable|in:title,regular_price,created_at,total_enrolled',
            'sort_order' => 'nullable|in:asc,desc',
            'per_page' => 'nullable|string|min:1|max:100',
            'page' => 'nullable|string|min:1',
        ]);

        $query = Course::query()
            ->where('institute_id', $this->getCurrentInstituteId());

        // Filters
        if ($request->filled('course_category_id')) {
            $query->where('course_category_id', $request->course_category_id);
        }

        if ($request->filled('course_sub_category_id')) {
            $query->where('course_sub_category_id', $request->course_sub_category_id);
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Eager loading & Select fields
        $query->with([
            'courseCategory:id,name',
            'courseSubCategory:id,name',
            'author:id,name',
            'zoomMeetings',
            'courseFaqs',
        ]);

        // Pagination
        $perPage = $request->input('per_page', 10);
        $courses = $query->paginate($perPage);

        return $this->responseSuccess($courses, 'Courses fetched successfully.');
    }

    public function store(CourseStoreRequest $request): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->courseRepository->create($request->all()),
                'Course has been created successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function show(int $id)
    {
        $course = Course::with([
            'courseChapters.contents' => function ($query) {
                $query->orderBy('serial');
            },
            'courseChapters.contents.contentable',
            'zoomMeetings',
        ])->where('id', $id)
            ->where('institute_id', $this->getCurrentInstituteId())
            ->first();
        if (! $course) {
            return $this->responseError([], 'Course not found', 404);
        }

        // Transform the output
        $transformed = [
            'course_id' => $course->id,
            'course_title' => $course->title,
            'course_slug' => $course->slug,
            'course_description' => $course->description,
            'course_image' => $course->image,
            'course_video_type' => $course->video_type,
            'course_video_url' => $course->video_url,
            'course_embedded_url' => $course->embedded_url,
            'course_uploaded_video_path' => $course->uploaded_video_path,
            'course_status' => $course->status,
            'course_publish_date' => $course->publish_date,
            'course_type' => $course->type,
            'course_payment_type' => $course->payment_type,
            'course_invoice_title' => $course->invoice_title,
            'course_regular_price' => $course->regular_price,
            'course_offer_price' => $course->offer_price,
            'course_repeat_count' => $course->repeat_count,
            'course_fake_enrolled_students' => $course->fake_enrolled_students,
            'course_total_classes' => $course->total_classes,
            'course_total_notes' => $course->total_notes,
            'course_total_exams' => $course->total_exams,
            'course_payment_duration' => $course->payment_duration,
            'course_total_cycles' => $course->total_cycles,
            'course_is_infinity' => $course->is_infinity,
            'course_is_auto_generate_invoice' => $course->is_auto_generate_invoice,
            'course_class_routine_image' => $course->class_routine_image,
            'course_meta_description' => $course->meta_description,
            'course_meta_keywords' => $course->meta_keywords,
            'course_total_view' => $course->total_view,
            'course_total_enrolled' => $course->total_enrolled,
            'course_created_by' => $course->created_by,
            'zoom_meetings' => $course->zoomMeetings->map(function ($meeting) {
                return [
                    'id' => $meeting->id,
                    'course_id' => $meeting->course_id,
                    'meeting_id' => $meeting->meeting_id,
                    'topic' => $meeting->topic,
                    'start_time' => $meeting->start_time,
                    'duration' => $meeting->duration,
                    'join_url' => $meeting->join_url,
                    'start_url' => $meeting->start_url,
                ];
            }),

            'course_chapters' => $course->courseChapters->map(function ($chapter) {
                return [
                    'chapter_id' => $chapter->id,
                    'chapter_title' => $chapter->title,
                    'chapter_description' => $chapter->description,
                    'chapter_priority' => $chapter->priority,
                    'contents' => $chapter->contents->map(function ($content) {
                        $contentable = $content->contentable;

                        // Basic fields
                        $entry = [
                            'content_id' => $content->id,
                            'type' => $content->type,
                            'type_id' => $content->type_id,
                            'serial' => $content->serial,
                            'title' => $contentable->title ?? null,
                        ];

                        // Add specific content type data
                        switch ($content->type) {
                            case 'lesson':
                                $entry['thumbnail_image'] = $contentable->thumbnail_image ?? null;
                                $entry['video_type'] = $contentable->video_type ?? null;
                                $entry['video_url'] = $contentable->video_url ?? null;
                                $entry['embedded_url'] = $contentable->embedded_url ?? null;
                                $entry['uploaded_video_path'] = $contentable->uploaded_video_path ?? null;
                                $entry['attachments'] = $contentable->attachments ?? null;
                                $entry['document_file'] = $contentable->document_file ?? null;
                                $entry['description'] = null;
                                $entry['is_scheduled'] = $contentable->is_scheduled ?? null;
                                $entry['scheduled_at'] = $contentable->scheduled_at ?? null;
                                $entry['playback_hours'] = $contentable->playback_hours ?? null;
                                $entry['playback_minutes'] = $contentable->playback_minutes ?? null;
                                $entry['playback_seconds'] = $contentable->playback_seconds ?? null;
                                $entry['visibility'] = $contentable->visibility ?? null;
                                $entry['password'] = $contentable->password ?? null;
                                $entry['priority'] = $contentable->priority ?? null;
                                $entry['created_by'] = $contentable->created_by ?? null;
                                $entry['status'] = $contentable->status ?? null;
                                break;

                            case 'quiz':
                                $entry['title'] = $contentable->title;
                                $entry['description'] = $contentable->description;
                                $entry['guidelines'] = $contentable->guidelines;
                                $entry['show_description_on_course_page'] = $contentable->show_description_on_course_page;
                                $entry['quiz_type'] = $contentable->type;
                                $entry['question_ids'] = $contentable->question_ids;
                                $entry['start_time'] = $contentable->start_time;
                                $entry['end_time'] = $contentable->end_time;
                                $entry['has_time_limit'] = $contentable->has_time_limit;
                                $entry['time_limit_value'] = $contentable->time_limit_value;
                                $entry['time_limit_unit'] = $contentable->time_limit_unit;
                                $entry['on_expiry'] = $contentable->on_expiry;
                                $entry['marks_per_question'] = $contentable->marks_per_question;
                                $entry['negative_marks_per_wrong_answer'] = $contentable->negative_marks_per_wrong_answer;
                                $entry['pass_mark'] = $contentable->pass_mark;
                                $entry['attempts_allowed'] = $contentable->attempts_allowed;
                                $entry['result_visibility'] = $contentable->result_visibility;
                                $entry['layout_pages'] = $contentable->layout_pages;
                                $entry['shuffle_questions'] = $contentable->shuffle_questions;
                                $entry['shuffle_options'] = $contentable->shuffle_options;
                                $entry['access_type'] = $contentable->access_type;
                                $entry['access_password'] = $contentable->access_password;
                                $entry['status'] = $contentable->status;
                                break;

                            case 'assignment':
                                $entry['deadline'] = $contentable->deadline ?? null;
                                $entry['file'] = $contentable->file ?? null;
                                break;

                            case 'pdf':
                                $entry['file'] = $contentable->file ?? null;
                                break;

                            case 'offline':
                                $entry['description'] = null;
                                break;
                        }

                        return $entry;
                    }),
                ];
            }),
        ];

        return $this->responseSuccess(
            $transformed,
            'Course has been fetched successfully.'
        );
    }

    public function update(CourseUpdateRequest $request, int $id): JsonResponse
    {
        try {
            return $this->responseSuccess(
                $this->courseRepository->update($id, $this->getUpdateRequest($request)),
                'Course has been updated successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $course = Course::with([
                'courseChapters',
                'courseFaqs',
                'enrollments',
                'zoomMeetings',
            ])->findOrFail($id);

            // ❌ Prevent deletion if these relationships exist
            if (
                $course->courseChapters->isNotEmpty() ||
                $course->enrollments->isNotEmpty() ||
                $course->zoomMeetings->isNotEmpty()
            ) {
                return $this->responseError([], 'Cannot delete course. Related data exists.', 409);
            }

            // ✅ Delete related FAQs
            $course->courseFaqs()->delete();

            // ✅ Delete the course
            $this->courseRepository->delete($id);

            return $this->responseSuccess([], 'Course and its FAQs have been deleted successfully.');
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode() ?: 500);
        }
    }

    public function courseStatusUpdate(Request $request): JsonResponse
    {
        $request->validate([
            'course_id' => 'required|integer|exists:courses,id',
            'status' => 'required|in:not_start,ongoing,completed',
        ]);

        try {
            return $this->responseSuccess(
                $this->courseRepository->courseStatusUpdate($request->all()),
                'Course status has been updated successfully.'
            );
        } catch (Exception $exception) {
            return $this->responseError([], $exception->getMessage(), $exception->getCode());
        }
    }

    public function courseList(Request $request)
    {
        $request->validate([
            'course_category_id' => 'nullable|integer|exists:course_categories,id',
            'course_sub_category_id' => 'nullable|integer|exists:course_categories,id',
            'search' => 'nullable|string|max:255',
            'status' => 'nullable|in:draft,schedule,published,private',
            'type' => 'nullable|in:single,bundle',
            'sort_by' => 'nullable|in:title,regular_price,created_at,total_enrolled',
            'sort_order' => 'nullable|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ]);

        $query = Course::query()
            ->where('institute_id', $this->getCurrentInstituteId());

        // Filters
        if ($request->filled('course_category_id')) {
            $query->where('course_category_id', $request->course_category_id);
        }

        if ($request->filled('course_sub_category_id')) {
            $query->where('course_sub_category_id', $request->course_sub_category_id);
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Eager loading & Select fields
        $query->with([
            'courseCategory:id,name',
            'courseSubCategory:id,name',
            'author:id,name',
            'zoomMeetings',
        ]);

        // Pagination
        $perPage = $request->input('per_page', 10);
        $courses = $query->paginate($perPage);

        return $this->responseSuccess($courses, 'Courses fetched successfully.');
    }

    public function courseDetails(int $courseId): JsonResponse
    {
        $course = Course::with('courseCategory', 'courseSubCategory', 'courseFaqs')
            ->where('id', $courseId)
            ->where('institute_id', $this->getCurrentInstituteId())->first();
        if (! $course) {
            return $this->responseError([], 'Course not found', 404);
        }

        return $this->responseSuccess($course, 'Course details fetched successfully.');
    }
}
