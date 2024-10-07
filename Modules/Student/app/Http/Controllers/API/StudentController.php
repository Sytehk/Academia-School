<?php

namespace Modules\Student\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Traits\Authenticatable;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Academic\Models\Student;
use Modules\Academic\Models\StudentAttendance;
use Modules\Accounting\Models\PaymentHistory;
use Modules\Authentication\Models\User;
use Modules\Elearning\Models\Course;
use Modules\Elearning\Models\Enrollment;
use Modules\Elearning\Models\Lesson;
use Modules\QuestionBank\Models\Quiz;
use Modules\QuestionBank\Models\QuizAttempt;
use Modules\Student\Http\Requests\Enrollment\EnrollmentStoreRequest;

class StudentController extends Controller
{
    use Authenticatable;

    public function profile(Request $request)
    {
        return 'Profile';
    }

    public function myCourse(Request $request)
    {
        try {
            $userId = getUserId();

            if (! $userId) {
                return $this->responseError([], 'User not found.', 404);
            }

            $courses = Enrollment::where('user_id', $userId)
                ->with(['course' => function ($query) {
                    $query->whereNull('deleted_at'); // soft delete filter
                }])
                ->get()
                ->filter(function ($enrollment) {
                    return $enrollment->course !== null; // skip deleted course
                })
                ->values();

            if ($courses->isEmpty()) {
                return $this->responseSuccess([], 'No enrolled courses found.');
            }

            return $this->responseSuccess(
                $courses,
                'Enrollment Courses retrieved successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], 'Something went wrong: '.$e->getMessage(), 500);
        }
    }

    /**
     * Courses in the student's own institute that are open for enrollment
     * (published only — draft/scheduled/private courses are not offered).
     * Each row carries is_enrolled so the client can show "Enroll" vs
     * "Already enrolled" without a second round trip.
     */
    public function availableCourses(Request $request): JsonResponse
    {
        $request->validate([
            'search' => 'nullable|string|max:255',
            'course_category_id' => 'nullable|integer',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $userId = Auth::id();

        $query = Course::where('institute_id', $this->getCurrentInstituteId())
            ->where('status', 'published')
            ->with('courseCategory:id,name');

        if ($request->filled('search')) {
            $query->where('title', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('course_category_id')) {
            $query->where('course_category_id', $request->course_category_id);
        }

        $courses = $query->orderByDesc('created_at')->paginate($request->input('per_page', 10));

        $enrolledCourseIds = Enrollment::where('user_id', $userId)
            ->whereIn('course_id', $courses->pluck('id'))
            ->pluck('course_id')
            ->all();

        $courses->getCollection()->transform(function ($course) use ($enrolledCourseIds) {
            $course->is_enrolled = in_array($course->id, $enrolledCourseIds, true);

            return $course;
        });

        return $this->responseSuccess($courses, 'Available courses fetched successfully.');
    }

    /**
     * Details of a specific available (published) course in the student's own
     * institute, for viewing before deciding to enroll — distinct from
     * myCourseDetails(), which is for a course already enrolled in.
     */
    public function courseDetails(int $id): JsonResponse
    {
        $course = Course::with(['courseCategory', 'courseSubCategory', 'courseFaqs'])
            ->where('id', $id)
            ->where('institute_id', $this->getCurrentInstituteId())
            ->where('status', 'published')
            ->first();

        if (! $course) {
            return $this->responseError([], 'Course not found.', 404);
        }

        $course->is_enrolled = Enrollment::where('user_id', Auth::id())->where('course_id', $course->id)->exists();

        return $this->responseSuccess($course, 'Course details fetched successfully.');
    }

    public function myCourseDetails($slug)
    {
        $course = Course::with([
            'courseChapters.contents' => function ($query) {
                $query->orderBy('serial');
            },
            'courseChapters.contents.contentable',
            'zoomMeetings',
        ])->where('slug', $slug)
            ->where('institute_id', $this->getCurrentInstituteId())
            ->first();

        if (! $course) {
            return $this->responseError([], 'Course not found.', 404);
        }

        // getUser info
        $user = getUser();
        if (! $user) {
            return $this->responseError([], 'User not found.', 404);
        }

        // "My" course details: content is only for a student actually enrolled
        // in it, not any authenticated user who knows/guesses the slug.
        $isEnrolled = Enrollment::where('user_id', $user->id)->where('course_id', $course->id)->exists();
        if (! $isEnrolled) {
            return $this->responseError([], 'You are not enrolled in this course.', 403);
        }
        // 🧩 STEP 1: Get user class & section (from auth or request)
        // $academicClassId = $user->academic_class_id;
        // $academicSectionId = $user->academic_section_id;
        // 🧩 STEP 2: Fetch all visible content IDs for this section
        $visibleContentIds = DB::table('content_visibility')
            ->where('course_id', $course->id)
            // ->where('academic_class_id', $academicClassId)
            // ->where('academic_section_id', $academicSectionId)
            ->where('is_enabled', true)
            ->pluck('content_id')
            ->toArray();
        // 🧩 STEP 3: Filter course chapters contents based on visibility
        foreach ($course->courseChapters as $chapter) {
            $chapter->setRelation('contents', $chapter->contents->filter(function ($content) use ($visibleContentIds) {
                // Keep only if visible OR if no visibility record exists (optional fallback)
                return in_array($content->id, $visibleContentIds);
            }));
        }

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
            'course_fake_enrolled_users' => $course->fake_enrolled_users,
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
                    'meeting_id' => $meeting->id,
                    'topic' => $meeting->topic,
                    'start_time' => $meeting->start_time,
                    'duration' => $meeting->duration,
                    'password' => $meeting->password,
                    'join_url' => $meeting->join_url,
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
                                $entry['enable_negative_marking'] = $contentable->enable_negative_marking;
                                $entry['result_visibility'] = $contentable->result_visibility;
                                $entry['layout_pages'] = $contentable->layout_pages;
                                $entry['shuffle_questions'] = $contentable->shuffle_questions;
                                $entry['shuffle_options'] = $contentable->shuffle_options;
                                $entry['access_type'] = $contentable->access_type;
                                $entry['access_password'] = $contentable->access_password;
                                $entry['status'] = $contentable->status;

                                if ($content->type_id) {
                                    $marksPerQuestion = $contentable->marks_per_question;
                                    $quizAttempts = QuizAttempt::with('result')
                                        ->where('quiz_id', $content->type_id)
                                        ->where('user_id', getStudentId())
                                        ->where('status', '!=', 'started')
                                        ->get();

                                    $allAttempts = QuizAttempt::where('quiz_id', $content->type_id)
                                        ->where('status', 'submitted')
                                        ->orderByDesc('score')
                                        ->pluck('user_id', 'id');

                                    $rankMap = [];
                                    $rank = 1;
                                    foreach ($allAttempts as $attemptId => $userId) {
                                        $rankMap[$attemptId] = $rank++;
                                    }

                                    $entry['quiz_attempts'] = $quizAttempts->map(function ($attempt) use ($rankMap, $marksPerQuestion) {
                                        $result = $attempt->result;
                                        $totalMarks = $result?->question_count * $marksPerQuestion;

                                        return [
                                            'attempt_id' => $attempt->id,
                                            'user_id' => $attempt->user_id,
                                            'score' => $attempt->score,
                                            'started_at' => $attempt->started_at,
                                            'submitted_at' => $attempt->submitted_at,
                                            'status' => $attempt->status,
                                            'result_summary' => $result ? [
                                                'result_id' => $result->id,
                                                'quiz_attempt_id' => $result->quiz_attempt_id,
                                                'score' => $result->score,
                                                'negative_marks' => $result->negative_marks,
                                                'grade_display' => number_format($result->score, 2).' / '.number_format($totalMarks, 2),
                                                'position_rank' => $rankMap[$attempt->id] ?? null,
                                                'total_question' => $result->question_count,
                                                'correct_question' => $result->correct_count,
                                                'wrong_question' => $result->incorrect_count,
                                                'skipped_question' => $result->skipped_count,
                                                'is_passed' => $result->is_passed,
                                            ] : null,
                                        ];
                                    });
                                }
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
                    })
                        ->values()
                        ->keyBy(function ($item, $key) {
                            return (string) $key;
                        }),
                ];
            }),
        ];

        return $this->responseSuccess(
            $transformed,
            'Course has been fetched successfully.'
        );
    }

    public function courseContentDetails(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:lesson,quiz',
            'type_id' => 'required|integer',
        ]);

        try {
            $content = match ($data['type']) {
                'lesson' => Lesson::select([
                    'id',
                    'institute_id',
                    'course_id',
                    'chapter_id',
                    'title',
                    'slug',
                    'description',
                    'thumbnail_image',
                    'video_type',
                    'video_url',
                    'embedded_url',
                    'uploaded_video_path',
                    'document_file',
                    'attachments',
                    'priority',
                    'playback_hours',
                    'playback_minutes',
                    'playback_seconds',
                    'is_scheduled',
                    'scheduled_at',
                    'visibility',
                    'password',
                    'status',
                    'created_by',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ])->where('status', 'active')->find($data['type_id']),

                'quiz' => Quiz::select([
                    'id',
                    'institute_id',
                    'course_id',
                    'chapter_id',
                    'created_by',
                    'priority',
                    'title',
                    'description',
                    'guidelines',
                    'show_description_on_course_page',
                    'type',
                    'question_ids',
                    'start_time',
                    'end_time',
                    'has_time_limit',
                    'time_limit_value',
                    'time_limit_unit',
                    'on_expiry',
                    'marks_per_question',
                    'negative_marks_per_wrong_answer',
                    'pass_mark',
                    'attempts_allowed',
                    'enable_negative_marking',
                    'result_visibility',
                    'layout_pages',
                    'shuffle_questions',
                    'shuffle_options',
                    'access_type',
                    'access_password',
                    'status',
                    'created_at',
                    'updated_at',
                ])->where('status', 'active')->find($data['type_id']),

                default => null
            };

            if (! $content) {
                return $this->responseError([], ucfirst($data['type']).' not found.', 404);
            }

            return $this->responseSuccess($content, ucfirst($data['type']).' has been fetched successfully.');
        } catch (\Throwable $e) {
            return $this->responseError(['error' => $e->getMessage()], 'Failed to fetch content details.', 500);
        }
    }

    public function myAttendance(Request $request)
    {
        try {
            // Get the authenticated user's ID
            $userId = getStudentId();

            if (! $userId) {
                return $this->responseError([], 'Student not found.', 404);
            }

            // Default date range: Current month's start and end
            $startDate = $request->input('start_date')
                ? Carbon::parse($request->start_date)->startOfDay()
                : Carbon::now()->startOfMonth()->startOfDay();

            $endDate = $request->input('end_date')
                ? Carbon::parse($request->end_date)->endOfDay()
                : Carbon::now()->endOfMonth()->endOfDay();

            // Query user attendance
            $attendanceQuery = StudentAttendance::where('user_id', $userId)
                // ->whereBetween('date', [$startDate, $endDate])
                // ->orderBy('date', 'desc') // Latest attendance first
                ->get();

            if ($attendanceQuery->isEmpty()) {
                return $this->responseError([], 'No attendance records found.', 404);
            }

            // Format the response
            $attendanceData = $attendanceQuery->map(function ($attendance) {
                return [
                    'course_id' => $attendance->course_id,
                    'course_name' => $attendance->course?->title ?? 'Unknown Course',
                    'date' => Carbon::parse($attendance->date)->format('Y-m-d'),
                    'attendance_status' => $attendance->attendance, // 'present', 'absent', 'not_marked'
                ];
            });

            return $this->responseSuccess($attendanceData, 'Attendance records retrieved successfully.');
        } catch (Exception $e) {
            return $this->responseError([], 'Something went wrong: '.$e->getMessage(), 500);
        }
    }

    // Student enrollment
    public function enrollmentCourse(EnrollmentStoreRequest $request): JsonResponse
    {
        // DB::beginTransaction(); // Start DB Transaction
        // try {
        $courseId = (int) $request->course_id;
        $userId = Auth::id();
        $enrollmentType = (string) $request->enrollment_type;

        $course = Course::where('institute_id', $this->getCurrentInstituteId())->find($courseId);
        $user = User::where('institute_id', $this->getCurrentInstituteId())->find($userId);

        if (! $course || ! $user) {
            return $this->responseError([], 'Invalid user or course.', 400);
        }

        // Only a published course is open for self-enrollment — draft/scheduled/
        // private courses are not yet (or not publicly) available.
        if ($course->status !== 'published') {
            return $this->responseError([], 'This course is not currently available for enrollment.', 422);
        }

        // Ensure user is active
        if ($user->status !== 1) {
            return $this->responseError([], 'Student is not active for enrollment.', 403);
        }

        // Check if user is already enrolled
        $existingEnrollment = Enrollment::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();

        // offer_price is the payable price (never null; defaults to 0, i.e. free —
        // regular_price is shown for comparison only and is never charged).
        $amountPaid = (float) $course->offer_price;

        if ($enrollmentType === 'monthly_subscription') {
            if ($existingEnrollment) {
                // If already enrolled, update subscription end date
                $existingEnrollment->subscription_end = Carbon::parse($existingEnrollment->subscription_end)->addMonth();
                $existingEnrollment->save();
            } else {
                // New enrollment for monthly subscription
                $existingEnrollment = Enrollment::create([
                    'institute_id' => $this->getCurrentInstituteId(),
                    'user_id' => $user->id,
                    'course_id' => $course->id,
                    'enrollment_type' => $enrollmentType,
                    'enrollment_date' => Carbon::now(),
                    'subscription_start' => Carbon::now(),
                    'subscription_end' => Carbon::now()->addMonth(),
                    'amount_paid' => $amountPaid,
                    'status' => 'active',
                ]);
            }
        } else {
            // One-time enrollment
            if ($existingEnrollment) {
                return $this->responseError([], 'Already enrolled in this course.', 400);
            }

            $existingEnrollment = Enrollment::create([
                'institute_id' => $this->getCurrentInstituteId(),
                'user_id' => $user->id,
                'course_id' => $course->id,
                'enrollment_type' => $enrollmentType,
                'enrollment_date' => Carbon::now(),
                'amount_paid' => $amountPaid,
                'status' => 'active',
            ]);
        }

        // Record the transaction
        $today = Carbon::now();
        $endOfMonth = $today->copy()->endOfMonth()->toDateString();
        PaymentHistory::create([
            'institute_id' => $this->getCurrentInstituteId(),
            'user_id' => $user->id,
            'course_id' => $course->id,
            // No payment method yet: this invoice is created Unpaid/Pending at
            // enrollment time, before the student has chosen how to pay.
            'payment_method_id' => null,
            'invoice_number' => 'INV'.time().$user->id,
            'invoice_title' => $enrollmentType == 'monthly_subscription' ? 'Monthly fee' : 'One-time fee',
            'payable' => $amountPaid,
            'paid' => 0,
            'due' => $amountPaid,
            'status' => 'Unpaid',
            'date_issued' => $today->toDateString(),
            'due_date' => $endOfMonth,
            'confirmation_status' => 'Pending',
        ]);
        //
        // DB::commit(); // Commit transaction

        return $this->responseSuccess(
            $existingEnrollment,
            'Course enrollment successfully completed.'
        );
        // } catch (Exception $e) {
        //     DB::rollBack(); // Rollback on error

        //     // Catch any exceptions and return a 500 error with the exception message
        //     return $this->responseError([], 'Failed to enroll in course. Please try again.');
        // }
    }

    // Student payment history
    public function myTransactions(Request $request)
    {
        $userId = (int) $request->user_id;
        $paymentHistories = PaymentHistory::with('paymentMethod')->where('user_id', $userId)->get();

        return $this->responseSuccess(
            $paymentHistories,
            'PaymentMethod has been fetched successfully.'
        );
    }
}
