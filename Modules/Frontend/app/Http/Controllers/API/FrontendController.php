<?php

namespace Modules\Frontend\Http\Controllers\API;

use App\Helpers\InstituteHelper;
use App\Http\Controllers\Controller;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Academic\Models\ClassModel;
use Modules\Academic\Models\Contact;
use Modules\Academic\Models\Event;
use Modules\Academic\Models\Notice;
use Modules\Academic\Models\Staff;
use Modules\Academic\Models\Student;
use Modules\Academic\Models\Teacher;
use Modules\Authentication\Models\Institute;
use Modules\Authentication\Models\InstituteImageSAASSetting;
use Modules\Authentication\Models\InstituteImageSetting;
use Modules\Authentication\Services\InstituteProvisioningService;
use Modules\Elearning\Models\Course;
use Modules\Elearning\Models\CourseCategory;
use Modules\Examination\Models\ClassExam;
use Modules\Frontend\Http\Requests\Contact\ContactStoreRequest;
use Modules\Frontend\Http\Requests\Onboarding\OnboardingStoreRequest;
use Modules\Frontend\Models\AboutUs;
use Modules\Frontend\Models\AcademicImage;
use Modules\Frontend\Models\Achievement;
use Modules\Frontend\Models\Banner;
use Modules\Frontend\Models\Benefit;
use Modules\Frontend\Models\FaqQuestion;
use Modules\Frontend\Models\HomeSection;
use Modules\Frontend\Models\MobileAppSection;
use Modules\Frontend\Models\Onboarding;
use Modules\Frontend\Models\OurHistory;
use Modules\Frontend\Models\Page;
use Modules\Frontend\Models\Policy;
use Modules\Frontend\Models\ReadyToJoinUs;
use Modules\Frontend\Models\Testimonial;
use Modules\Frontend\Models\Theme;
use Modules\Frontend\Models\WhyChooseUs;
use Throwable;

class FrontendController extends Controller
{
    public function bootstrap(Request $request): JsonResponse
    {
        $instituteId = $this->getInstituteIdFromHeader($request);
        if ($instituteId instanceof JsonResponse) {
            return $instituteId;
        }

        $cacheKey = 'frontend_bootstrap_'.$instituteId;
        $data = [

            /*
            |--------------------------------------------------------------------------
            | General
            |--------------------------------------------------------------------------
            */
            'pages' => Page::where('institute_id', $instituteId)
                ->select('id', 'title', 'slug')
                ->get(),

            'settings' => DB::table('settings')
                ->whereIn('name', $this->publicSettingKeys())
                ->where('institute_id', $instituteId)
                ->pluck('value', 'name'),

            'student_count' => Student::where('institute_id', $instituteId)->count(),

            'home_sections' => HomeSection::where('institute_id', $instituteId)
                ->orderBy('serial')
                ->get(),

            /*
            |--------------------------------------------------------------------------
            | Home
            |--------------------------------------------------------------------------
            */

            'banners' => Banner::where('institute_id', $instituteId)
                ->get(),

            'about' => AboutUs::where('institute_id', $instituteId)
                ->first(),

            'why_choose_us' => WhyChooseUs::where('institute_id', $instituteId)
                ->select('id', 'title', 'description', 'icon')
                ->get(),

            'ready_to_join_us' => ReadyToJoinUs::where('institute_id', $instituteId)
                ->get(),

            /*
            |--------------------------------------------------------------------------
            | Staff
            |--------------------------------------------------------------------------
            */

            'teachers' => Teacher::with('user:id,name,image')
                ->where('institute_id', $instituteId)
                ->select(
                    'id',
                    'user_id',
                    'name',
                    'designation'
                )
                ->limit(4)
                ->get(),

            'staffs' => Staff::with('user:id,name,image')
                ->where('institute_id', $instituteId)
                ->select(
                    'id',
                    'user_id',
                    'name',
                    'designation'
                )
                ->limit(4)
                ->get(),

            /*
            |--------------------------------------------------------------------------
            | Event
            |--------------------------------------------------------------------------
            */

            'events' => Event::where('institute_id', $instituteId)
                ->latest()
                ->take(6)
                ->get(),

            'notices' => Notice::with('userType')
                ->where('institute_id', $instituteId)
                ->latest()
                ->take(5)
                ->get(),

            /*
            |--------------------------------------------------------------------------
            | Others
            |--------------------------------------------------------------------------
            */

            'faqs' => FaqQuestion::where('institute_id', $instituteId)
                ->get(),

            'testimonials' => Testimonial::where('institute_id', $instituteId)
                ->get(),

            'mobile_app' => MobileAppSection::where('institute_id', $instituteId)
                ->get(),

            'gallery' => AcademicImage::where('institute_id', $instituteId)
                ->get(),

            'benefits' => Benefit::where('institute_id', $instituteId)
                ->get(),

            'achievements' => Achievement::where('institute_id', $instituteId)
                ->latest()
                ->get(),

            /*
            |--------------------------------------------------------------------------
            | Policies
            |--------------------------------------------------------------------------
            */

            'privacy_policy' => Policy::where('institute_id', $instituteId)
                ->whereType(1)
                ->first(),

            'cookie_policy' => Policy::where('institute_id', $instituteId)
                ->whereType(2)
                ->first(),

            'terms_conditions' => Policy::where('institute_id', $instituteId)
                ->whereType(3)
                ->first(),

            'refund_policy' => Policy::where('institute_id', $instituteId)
                ->whereType(4)
                ->first(),

            'legal' => Policy::where('institute_id', $instituteId)
                ->whereType(5)
                ->first(),

            'legal_pages' => Policy::where('institute_id', $instituteId)
                ->where('status', 1)
                ->whereNull('deleted_at')
                ->get(['id', 'type', 'status'])
                ->map(function ($policy) {
                    return [
                        'id' => $policy->id,
                        'type' => (int) $policy->type,
                        'slug' => $this->policySlug((int) $policy->type),
                        'title' => $this->policyTitle((int) $policy->type),
                    ];
                }),

            /*
            |--------------------------------------------------------------------------
            | Images
            |--------------------------------------------------------------------------
            */

            'image_settings' => InstituteImageSetting::where('institute_id', $instituteId)
                ->first(),
        ];

        return $this->responseSuccess(
            $data,
            'Frontend bootstrap fetched successfully.'
        );
    }

    public function homeSections(Request $request): JsonResponse
    {
        $instituteId = $this->getInstituteIdFromHeader($request);
        if ($instituteId instanceof JsonResponse) {
            return $instituteId;
        }

        try {
            $data = HomeSection::where('institute_id', $instituteId)
                ->whereNull('deleted_at')
                ->orderBy('serial')
                ->get();

            return $this->responseSuccess(
                $data,
                'Home Section has been fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function achievements(Request $request): JsonResponse
    {
        $instituteId = $this->getInstituteIdFromHeader($request);
        if ($instituteId instanceof JsonResponse) {
            return $instituteId;
        }

        try {
            $data = Achievement::where('institute_id', $instituteId)
                ->whereNull('deleted_at')
                ->latest()->first();

            return $this->responseSuccess(
                $data,
                'Achievements has been fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function benefits(Request $request): JsonResponse
    {
        $instituteId = $this->getInstituteIdFromHeader($request);
        if ($instituteId instanceof JsonResponse) {
            return $instituteId;
        }

        try {
            $data = Benefit::where('institute_id', $instituteId)
                ->whereNull('deleted_at')
                ->get();

            return $this->responseSuccess(
                $data,
                'Benefits has been fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function banners(Request $request): JsonResponse
    {
        $instituteId = $this->getInstituteIdFromHeader($request);
        if ($instituteId instanceof JsonResponse) {
            return $instituteId;
        }

        try {
            $data = Banner::where('institute_id', $instituteId)
                ->whereNull('deleted_at')
                ->get();

            return $this->responseSuccess(
                $data,
                'Banners has been fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function aboutUs(Request $request): JsonResponse
    {
        $instituteId = $this->getInstituteIdFromHeader($request);
        if ($instituteId instanceof JsonResponse) {
            return $instituteId;
        }

        try {
            $data = AboutUs::where('institute_id', $instituteId)->where('status', 1)->whereNull('deleted_at')->first();

            return $this->responseSuccess(
                $data,
                'AboutUs has been fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function academicImages(Request $request): JsonResponse
    {
        $instituteId = $this->getInstituteIdFromHeader($request);
        if ($instituteId instanceof JsonResponse) {
            return $instituteId;
        }

        try {
            $data['pageTitle'] = 'Academic Images';
            $data['academicImages'] = AcademicImage::where('institute_id', $instituteId)->whereNull('deleted_at')->get();

            return $this->responseSuccess(
                $data,
                'AcademicImages has been fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function whyChooseUs(Request $request): JsonResponse
    {
        $instituteId = $this->getInstituteIdFromHeader($request);
        if ($instituteId instanceof JsonResponse) {
            return $instituteId;
        }

        try {
            $data = WhyChooseUs::select(
                'id',
                'institute_id',
                'branch_id',
                'title',
                'description',
                'icon',
                'created_at',
            )
                ->where('institute_id', $instituteId)
                ->whereNull('deleted_at')
                ->get();

            return $this->responseSuccess(
                $data,
                'WhyChooseUs has been fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function readyToJoinUs(Request $request): JsonResponse
    {
        $instituteId = $this->getInstituteIdFromHeader($request);
        if ($instituteId instanceof JsonResponse) {
            return $instituteId;
        }

        try {
            $data = ReadyToJoinUs::select(
                'id',
                'institute_id',
                'branch_id',
                'title',
                'description',
                'icon',
                'button_name',
                'button_link',
                'created_at',
            )
                ->where('institute_id', $instituteId)
                ->whereNull('deleted_at')
                ->get();

            return $this->responseSuccess(
                $data,
                'ReadyToJoinUs has been fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function teachers(Request $request): JsonResponse
    {
        $instituteId = $this->getInstituteIdFromHeader($request);
        if ($instituteId instanceof JsonResponse) {
            return $instituteId;
        }

        try {
            $data = Teacher::with('user')->select(
                'id',
                'institute_id',
                'branch_id',
                'user_id',
                'department_id',
                'name',
                'designation',
                'sl',
                'status',
                'created_at',
            )
                ->where('institute_id', $instituteId)
                ->whereNull('deleted_at')
                ->get();

            return $this->responseSuccess(
                $data,
                'Teachers has been fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function staffs(Request $request): JsonResponse
    {
        $instituteId = $this->getInstituteIdFromHeader($request);
        if ($instituteId instanceof JsonResponse) {
            return $instituteId;
        }

        try {
            $data = Staff::with('user')->select(
                'id',
                'institute_id',
                'branch_id',
                'user_id',
                'department_id',
                'name',
                'designation',
                'sl',
                'status',
                'created_at',
            )
                ->where('institute_id', $instituteId)
                ->whereNull('deleted_at')
                ->get();

            return $this->responseSuccess(
                $data,
                'Staff has been fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function events(Request $request): JsonResponse
    {
        $instituteId = $this->getInstituteIdFromHeader($request);
        if ($instituteId instanceof JsonResponse) {
            return $instituteId;
        }

        try {
            $data = Event::select(
                'id',
                'institute_id',
                'branch_id',
                'start_date',
                'end_date',
                'name',
                'image',
                'details',
                'location',
                'created_at',
            )
                ->where('institute_id', $instituteId)
                ->get();

            return $this->responseSuccess(
                $data,
                'Events has been fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function notices(Request $request): JsonResponse
    {
        $instituteId = $this->getInstituteIdFromHeader($request);
        if ($instituteId instanceof JsonResponse) {
            return $instituteId;
        }

        try {
            $data = Notice::select('id', 'title', 'notice', 'date', 'image', 'created_by')->with('userType')
                ->where('institute_id', $instituteId)
                ->whereNull('deleted_at')
                ->limit(5)
                ->get();

            return $this->responseSuccess(
                $data,
                'Notices has been fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function faq(Request $request): JsonResponse
    {
        $instituteId = $this->getInstituteIdFromHeader($request);
        if ($instituteId instanceof JsonResponse) {
            return $instituteId;
        }

        try {
            $data = FaqQuestion::where('institute_id', $instituteId)
                ->select('id', 'institute_id', 'question', 'answer')
                ->whereNull('deleted_at')
                ->get();

            return $this->responseSuccess(
                $data,
                'Faqs has been fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function privacyPolicy(Request $request): JsonResponse
    {
        $instituteId = $this->getInstituteIdFromHeader($request);
        if ($instituteId instanceof JsonResponse) {
            return $instituteId;
        }

        try {
            $data = Policy::where('institute_id', $instituteId)->select('id', 'institute_id', 'type', 'description')->whereType(1)->whereNull('deleted_at')->first();

            return $this->responseSuccess(
                $data,
                'Privacy Policy has been fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function cookiePolicy(Request $request): JsonResponse
    {
        $instituteId = $this->getInstituteIdFromHeader($request);
        if ($instituteId instanceof JsonResponse) {
            return $instituteId;
        }

        try {
            $data = Policy::where('institute_id', $instituteId)->select('id', 'institute_id', 'type', 'description')->whereType(2)->whereNull('deleted_at')->first();

            return $this->responseSuccess(
                $data,
                'Cookie Policy has been fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function termConditions(Request $request): JsonResponse
    {
        $instituteId = $this->getInstituteIdFromHeader($request);
        if ($instituteId instanceof JsonResponse) {
            return $instituteId;
        }

        try {
            $data = Policy::where('institute_id', $instituteId)->select('id', 'institute_id', 'type', 'description')->whereType(3)->whereNull('deleted_at')->first();

            return $this->responseSuccess(
                $data,
                'Terms & Conditions has been fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function refundPolicy(Request $request): JsonResponse
    {
        return $this->policyByType($request, 4, 'Refund Policy has been fetched successfully.');
    }

    public function legal(Request $request): JsonResponse
    {
        return $this->policyByType($request, 5, 'Legal page has been fetched successfully.');
    }

    public function academicImageShow(Request $request, int $id): JsonResponse
    {
        $instituteId = $this->getInstituteIdFromHeader($request);
        if ($instituteId instanceof JsonResponse) {
            return $instituteId;
        }

        try {
            $data = AcademicImage::where('institute_id', $instituteId)
                ->where('id', $id)
                ->whereNull('deleted_at')
                ->first();

            if (! $data) {
                return $this->responseError([], 'Gallery image not found.', 404);
            }

            return $this->responseSuccess(
                $data,
                'Academic image has been fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function ourHistory(Request $request): JsonResponse
    {
        $instituteId = $this->getInstituteIdFromHeader($request);
        if ($instituteId instanceof JsonResponse) {
            return $instituteId;
        }

        try {
            $data = OurHistory::where('institute_id', $instituteId)
                ->select(
                    'institute_id',
                    'year',
                    'title',
                    'descriptions',
                    'status',
                    'created_by',
                )
                ->whereNull('deleted_at')
                ->first();

            return $this->responseSuccess(
                $data,
                'Our History has been fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function testimonials(Request $request): JsonResponse
    {
        $instituteId = $this->getInstituteIdFromHeader($request);
        if ($instituteId instanceof JsonResponse) {
            return $instituteId;
        }

        try {
            $data = Testimonial::select(
                'institute_id',
                'branch_id',
                'name',
                'description',
                'thumbnail_image',
                'video_url',
                'note',
                'ratting',
                'status',
            )
                ->where('institute_id', $instituteId)
                ->whereNull('deleted_at')
                ->get();

            return $this->responseSuccess(
                $data,
                'Testimonials has been fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function mobileAppSections(Request $request): JsonResponse
    {
        $instituteId = $this->getInstituteIdFromHeader($request);
        if ($instituteId instanceof JsonResponse) {
            return $instituteId;
        }

        try {
            $data = MobileAppSection::select(
                'id',
                'institute_id',
                'branch_id',
                'title',
                'heading',
                'description',
                'image',
                'feature_one',
                'feature_two',
                'feature_three',
                'play_store_link',
                'app_store_link',
            )
                ->where('institute_id', $instituteId)
                ->whereNull('deleted_at')
                ->get();

            return $this->responseSuccess(
                $data,
                'MobileAppSections has been fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function courses(Request $request): JsonResponse
    {
        $instituteId = $this->getInstituteIdFromHeader($request);
        if ($instituteId instanceof JsonResponse) {
            return $instituteId;
        }

        try {
            $query = Course::where('institute_id', $instituteId);

            // Search by category_id (optional)
            if ($request->has('course_category_id') && ! empty($request->course_category_id)) {
                $query->where('course_category_id', $request->course_category_id);
            }

            // Search by title (optional)
            if ($request->has('title') && ! empty($request->title)) {
                $query->where('title', 'like', '%'.$request->title.'%');
            }

            // Search by price range (optional)
            if ($request->has('min_price') && ! empty($request->min_price)) {
                $query->where('price', '>=', $request->min_price);
            }
            if ($request->has('max_price') && ! empty($request->max_price)) {
                $query->where('price', '<=', $request->max_price);
            }

            // Search by status (optional)
            if ($request->has('status') && ! empty($request->status)) {
                $query->where('status', $request->status);
            }

            // Pagination logic: Default to 10 items per page
            $perPage = $request->get('perPage', 10); // Default to 10 if not provided
            $data = $query->paginate($perPage);

            return $this->responseSuccess(
                $data,
                'Courses fetched successfully with the applied filters.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function settings(Request $request): JsonResponse
    {
        $instituteId = $this->getInstituteIdFromHeader($request);
        if ($instituteId instanceof JsonResponse) {
            return $instituteId;
        }

        try {
            $allowedKeys = $this->publicSettingKeys();

            $settings = DB::table('settings')
                ->whereIn('name', $allowedKeys)
                ->where('institute_id', $instituteId)
                ->pluck('value', 'name');

            return $this->responseSuccess(
                $settings,
                'Settings have been fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function categoryWiseCourses(Request $request): JsonResponse
    {
        $instituteId = $this->getInstituteIdFromHeader($request);
        if ($instituteId instanceof JsonResponse) {
            return $instituteId;
        }

        try {
            $data['pageTitle'] = 'Course Categories and Courses';
            // Get the first 5 CourseCategories and load the related courses
            $data['courseCategories'] = CourseCategory::where('institute_id', $instituteId)
                ->whereHas('courses') // Ensures that only categories with courses are fetched
                ->with('courses')
                ->select('id', 'institute_id', 'name', 'slug', 'status', 'created_at')
                ->limit(5)->get();

            return $this->responseSuccess(
                $data,
                'Course Categories and Courses has been fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function eventDetails(Request $request, int $id): JsonResponse
    {
        $instituteId = $this->getInstituteIdFromHeader($request);
        if ($instituteId instanceof JsonResponse) {
            return $instituteId;
        }

        try {
            $data = Event::select(
                'id',
                'institute_id',
                'branch_id',
                'start_date',
                'end_date',
                'name',
                'image',
                'details',
                'location',
                'created_at',
            )
                ->where('institute_id', $instituteId)
                ->where('id', (int) $id)
                ->first();

            return $this->responseSuccess(
                $data,
                'Event has been fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function pages(Request $request)
    {
        $instituteId = $this->getInstituteIdFromHeader($request);
        if ($instituteId instanceof JsonResponse) {
            return $instituteId;
        }

        $pages = Page::where('institute_id', $instituteId)->get();

        return $this->responseSuccess(
            $pages,
            'Pages get successfully.'
        );
    }

    public function instituteImageSetting(Request $request)
    {
        $instituteId = $this->getInstituteIdFromHeader($request);
        if ($instituteId instanceof JsonResponse) {
            return $instituteId;
        }

        try {
            $data = InstituteImageSetting::where('institute_id', $instituteId)->first();

            return $this->responseSuccess(
                $data,
                'Institute Settings Image has been fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function courseDetails(Request $request, string $slug): JsonResponse
    {
        $instituteId = $this->getInstituteIdFromHeader($request);
        if ($instituteId instanceof JsonResponse) {
            return $instituteId;
        }

        $course = Course::with([
            'courseChapters.contents' => function ($query) {
                $query->orderBy('serial');
            },
            'courseChapters.contents.contentable',
            'zoomMeetings',
            'courseFaqs',
        ])->where('slug', $slug)
            ->where('institute_id', $instituteId)
            ->first();
        if (! $course) {
            return $this->responseError([], 'Course not found', 404);
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
            'course_fake_enrolled_students' => $course->fake_enrolled_students + $course->total_enrolled,
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
            'course_faqs' => $course->courseFaqs,
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

    private function getInstituteIdFromHeader(Request $request): mixed
    {
        $domain = $request->header('X-Domain'); // Custom header key (Change if needed)
        if (! $domain) {
            return $this->responseError([], 'Domain header is missing.', 400);
        }

        $instituteResponse = InstituteHelper::getInstituteIdByDomain($domain);
        if (! $instituteResponse['status']) {
            return $this->responseError([], $instituteResponse['error'], 404);
        }

        return $instituteResponse['institute_id'];
    }

    public function contactUs(ContactStoreRequest $request): JsonResponse
    {
        try {
            $instituteId = $this->getInstituteIdFromHeader($request);
            if ($instituteId instanceof JsonResponse) {
                return $instituteId;
            }

            // Store Contact
            $contact = Contact::create([
                'institute_id' => $instituteId,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'message' => $request->message,
            ]);

            return $this->responseSuccess(
                $contact,
                'Contact form submitted successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function themes(Request $request)
    {
        $themes = Theme::get();

        return $this->responseSuccess(
            $themes,
            'themes get successfully.'
        );
    }

    public function pageShow(Request $request, string $slug)
    {
        $instituteId = $this->getInstituteIdFromHeader($request);
        if ($instituteId instanceof JsonResponse) {
            return $instituteId;
        }

        $page = Page::where('slug', $slug)
            ->where('institute_id', $instituteId)
            ->firstOrFail();

        return $this->responseSuccess(
            $page,
            'page get successfully.'
        );
    }

    public function getClasses(Request $request): JsonResponse
    {
        $instituteId = $this->getInstituteIdFromHeader($request);
        if ($instituteId instanceof JsonResponse) {
            return $instituteId;
        }

        try {
            $data = ClassModel::where('institute_id', $instituteId)
                ->get();

            return $this->responseSuccess(
                $data,
                'Classes has been fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    public function getClassExam(Request $request): JsonResponse
    {
        $instituteId = $this->getInstituteIdFromHeader($request);
        if ($instituteId instanceof JsonResponse) {
            return $instituteId;
        }

        try {
            $data = ClassExam::where('class_id', $request->class_id)->with('exam')->get();

            return $this->responseSuccess(
                $data,
                'Classes has been fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    /**
     * Public institute sign-up (API).
     *
     * Shares {@see InstituteProvisioningService} with the web sign-up flow and the
     * admin approval endpoint. This previously inlined a third copy of the
     * provisioning logic carrying the same defects: no transaction, 'type' and
     * 'avatar' written to attributes that do not exist on the models (silently
     * discarding the submitted institute type and avatar), the already-hashed
     * password re-hashed, and the onboarding record never stamped approved — so
     * completed sign-ups stayed in the pending list forever.
     */
    public function onboarding(OnboardingStoreRequest $request, InstituteProvisioningService $provisioner): JsonResponse
    {
        $validated = $request->validated();

        // Uploaded outside the transaction: the filesystem is not transactional.
        $instituteLogo = $request->hasFile('institute_logo')
            ? fileUploader('institutes/', 'png', $request->file('institute_logo'))
            : null;

        $userAvatar = $request->hasFile('user_avatar')
            ? fileUploader('user_avatars/', 'png', $request->file('user_avatar'))
            : null;

        try {
            $result = DB::transaction(function () use ($validated, $instituteLogo, $userAvatar, $provisioner) {
                $onboarding = Onboarding::create([
                    'collected_data' => Onboarding::collectedDataFrom($validated),
                    'institute_logo' => $instituteLogo,
                    'user_avatar' => $userAvatar,
                    'status' => Onboarding::STATUS_PENDING,
                ]);

                return $provisioner->provisionFromOnboarding($onboarding);
            });
        } catch (Throwable $e) {
            Log::error('Institute onboarding failed', [
                'institute_email' => $validated['institute_email'] ?? null,
                'error' => $e->getMessage(),
            ]);

            return $this->responseError([], 'We could not complete your registration. Please try again.', 500);
        }

        $provisioner->sendWelcomeMail($result, $validated['password']);

        $user = $result['user'];

        return $this->responseSuccess([
            'user' => $user,
            'access_token' => $user->createToken('app')->accessToken,
        ], 'Onboarding request submitted successfully.');
    }

    public function defaultTheme(Request $request)
    {
        $instituteId = $this->getInstituteIdFromHeader($request);
        if ($instituteId instanceof JsonResponse) {
            return $instituteId;
        }

        $institute = Institute::with('theme')
            ->find($instituteId);

        if (! $institute) {
            return $this->responseError([], 'Institute not found', 404);
        }

        return $this->responseSuccess(
            $institute->theme,
            'Default theme retrieved successfully.'
        );
    }

    private function addDomainToVercel($fullDomain)
    {
        $client = new Client;

        $projectId = get_saas_option('vercel_project_id');
        $token = get_saas_option('vercel_token');
        $url = "https://api.vercel.com/v9/projects/{$projectId}/domains";

        try {
            $response = $client->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer '.$token,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'name' => $fullDomain,
                ],
            ]);

            return [
                'success' => true,
                'data' => json_decode($response->getBody(), true),
            ];
        } catch (ClientException $e) {
            return [
                'success' => false,
                'error_type' => 'client',
                'message' => $e->getMessage(),
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error_type' => 'general',
                'message' => $e->getMessage(),
            ];
        }
    }

    public function instituteImageSAASSetting(Request $request)
    {
        try {
            $data = InstituteImageSAASSetting::first();

            return $this->responseSuccess(
                $data,
                'Institute SAAS Settings Image has been fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    private function publicSettingKeys(): array
    {
        return [
            'school_name',
            'site_title',
            'phone',
            'email',
            'language',
            'google_map',
            'address',
            'timezone',
            'academic_year',
            'currency_symbol',
            'logo',
            'copyright_text',
            'tagline',
            'meta_title',
            'meta_description',
            'meta_keywords',
            'og_image',
            'ga4_id',
            'gtm_id',
            'meta_pixel_id',
            'facebook_link',
            'google_plus_link',
            'youtube_link',
            'whats_app_link',
            'twitter_link',
            'linkedin_link',
            'eiin_code',
            'header_notice',
            'exam_result_status',
            'admission_display_status',
            'guidance',
            'academic_office',
            'website_link',
            'play_store_link',
            'app_store_link',
            'tuition_fee_phone',
            'exam_result_phone',
            'primary_color',
            'secondary_color',
            'primary_container_color',
            'dark_primary_color',
            'dark_secondary_color',
            'dark_container_color',
            'text_color',
            'dark_text_color',
            'sidebar_selected_bg_color',
            'sidebar_selected_text_color',
        ];
    }

    private function policyByType(Request $request, int $type, string $message): JsonResponse
    {
        $instituteId = $this->getInstituteIdFromHeader($request);
        if ($instituteId instanceof JsonResponse) {
            return $instituteId;
        }

        try {
            $data = Policy::where('institute_id', $instituteId)
                ->select('id', 'institute_id', 'type', 'description')
                ->whereType($type)
                ->whereNull('deleted_at')
                ->first();

            return $this->responseSuccess($data, $message);
        } catch (Exception $e) {
            return $this->responseError([], $e->getMessage());
        }
    }

    private function policySlug(int $type): string
    {
        return match ($type) {
            1 => 'privacy-policy',
            2 => 'cookies-policy',
            3 => 'terms-conditions',
            4 => 'refund-policy',
            5 => 'legal',
            default => 'policy',
        };
    }

    private function policyTitle(int $type): string
    {
        return match ($type) {
            1 => 'Privacy Policy',
            2 => 'Cookies Policy',
            3 => 'Terms & Conditions',
            4 => 'Refund Policy',
            5 => 'Legal',
            default => 'Policy',
        };
    }
}
