<?php

namespace Modules\Elearning\Repositories;

use App\Abstracts\EntityRepository;
use App\Traits\SlugAbleTrait;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Modules\Elearning\Models\Course;
use Modules\Elearning\Models\CourseFaq;
use Symfony\Component\HttpFoundation\Response;

class CourseRepository extends EntityRepository
{
    use SlugAbleTrait;

    public string $table = Course::TABLE_NAME;

    protected array $fillableColumns = [
        'institute_id',
        'course_category_id',
        'course_sub_category_id',
        'title',
        'slug',
        'image',
        'video_type',
        'video_url',
        'embedded_url',
        'uploaded_video_path',
        'status',
        'publish_date',
        'type',
        'payment_type',
        'invoice_title',
        'regular_price',
        'offer_price',
        'repeat_count',
        'fake_enrolled_students',
        'total_classes',
        'total_notes',
        'total_exams',
        'payment_duration',
        'total_cycles',
        'is_infinity',
        'is_auto_generate_invoice',
        'description',
        'class_routine_image',
        'meta_description',
        'meta_keywords',
        'total_view',
        'total_enrolled',
        'created_by',
    ];

    protected function getQuery(): Builder
    {
        return parent::getQuery();
    }

    public function getAll(array $filterData = []): Paginator
    {
        $filter = $this->getFilterData($filterData);
        $query = $this->getCourseQuery();

        if (! $filter['with_deleted']) {
            $query->whereNull("{$this->table}.deleted_at");
        }

        if (! empty($filter['course_category_id'])) {
            $query->where("{$this->table}.course_category_id", $filter['course_category_id']);
        }

        if (isset($filter['search']) && strlen($filter['search']) > 0) {
            $query = $this->filterSearchQuery($query, $filter['search']);
        }

        return $query
            ->orderBy($filter['orderBy'], $filter['order'])
            ->paginate($filter['perPage']);
    }

    protected function getFilterData(array $filterData = []): array
    {
        $defaultArgs = [
            'perPage' => 10,
            'search' => '',
            'orderBy' => 'id',
            'order' => 'desc',
            'with_deleted' => false,
            'course_category_id' => null, // Default to null
        ];

        return array_merge($defaultArgs, $filterData);
    }

    private function getCourseQuery(): Builder
    {
        return $this->getQuery()
            ->leftJoin('course_categories', 'courses.course_category_id', '=', 'course_categories.id')
            ->leftJoin('users as user_created_by', 'courses.created_by', '=', 'user_created_by.id')
            ->select(
                'courses.id',
                'courses.institute_id',
                'courses.course_category_id',
                'course_categories.name as category_name',
                'courses.title',
                'courses.slug',
                'courses.image',
                'courses.video_type',
                'courses.video_url',
                'courses.embedded_url',
                'courses.uploaded_video_path',
                'courses.status',
                'courses.publish_date',
                'courses.type',
                'courses.payment_type',
                'courses.invoice_title',
                'courses.regular_price',
                'courses.offer_price',
                'courses.repeat_count',
                'courses.fake_enrolled_students',
                'courses.total_classes',
                'courses.total_notes',
                'courses.total_exams',
                'courses.payment_duration',
                'courses.total_cycles',
                'courses.is_infinity',
                'courses.is_auto_generate_invoice',
                'courses.description',
                'courses.class_routine_image',
                'courses.meta_description',
                'courses.meta_keywords',
                'courses.total_view',
                'courses.total_enrolled',
                'courses.created_by',
                'user_created_by.name as author_name',
                'user_created_by.image as author_image',
                'courses.created_at',
                'courses.updated_at',
                'courses.deleted_at'
            );
    }

    protected function filterSearchQuery(Builder|EloquentBuilder $query, string $searchedText): Builder
    {
        $searchable = "%$searchedText%";

        return $query->where('courses.title', 'LIKE', $searchable)
            ->orWhere('courses.course_category_id', 'LIKE', $searchable)
            ->orWhere('courses.slug', 'LIKE', $searchable)
            ->orWhere('courses.regular_price', 'LIKE', $searchable)
            ->orWhere('courses.offer_price', 'LIKE', $searchable)
            ->orWhere('courses.type', 'LIKE', $searchable)
            ->orWhere('courses.status', 'LIKE', $searchable);
    }

    /**
     * @throws Exception
     */
    public function getByColumn(string $columnName, $columnValue, array $selects = ['*']): ?object
    {
        $user = $this->getCourseQuery()
            ->where($columnName, $columnValue)
            ->first();

        if (empty($user)) {
            throw new Exception(
                $this->getExceptionMessage(static::MESSAGE_ITEM_DOES_NOT_EXIST_MESSAGE),
                Response::HTTP_NOT_FOUND
            );
        }

        return $user;
    }

    public function getCount(array $filterData = []): int
    {
        $filter = $this->getFilterData($filterData);

        $query = $this->getQuery();

        if (! $filter['with_deleted']) {
            $query->whereNull("{$this->table}.deleted_at");
        }

        return $query->count();
    }

    /**
     * @throws Exception
     */
    public function create(array $data): object
    {
        DB::beginTransaction();
        try {
            // Extract FAQs and remove from main $data
            $faqsJson = $data['faqs'] ?? null;
            // unset($data['faqs']);

            $data = $this->prepareForDB($data);
            // Store Course
            $courseId = $this->getQuery()->insertGetId($data);
            $course = Course::findOrFail($courseId);

            // Handle FAQs
            if (! empty($faqsJson)) {
                $faqs = json_decode($faqsJson, true);

                // Handle FAQs
                if (! empty($faqsJson)) {
                    $decodedOnce = json_decode($faqsJson, true);
                    if (is_string($decodedOnce)) {
                        $faqs = json_decode($decodedOnce, true); // Decode again if it's still a string
                    } else {
                        $faqs = $decodedOnce;
                    }

                    if (is_array($faqs)) {
                        foreach ($faqs as $faq) {
                            if (
                                isset($faq['question'], $faq['answer']) &&
                                ! empty(trim($faq['question'])) &&
                                ! empty(trim($faq['answer']))
                            ) {
                                CourseFaq::create([
                                    'course_id' => $course->id,
                                    'institute_id' => $this->getCurrentInstituteId(),
                                    'question' => $faq['question'],
                                    'answer' => $faq['answer'],
                                ]);
                            }
                        }
                    }
                }
            }

            DB::commit();

            return $course;
        } catch (Exception $exception) {
            DB::rollBack();
            throw new Exception($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @throws Exception
     */
    public function prepareForDB(array $data, ?object $item = null): array
    {
        $data = parent::prepareForDB($data, $item);
        if (empty($item)) {
            $data['created_at'] = now();
            $data['created_by'] = $this->getCurrentUserId();
            $data['institute_id'] = $this->getCurrentInstituteId();
            $data['branch_id'] = $this->getCurrentBranchId();

            if (! empty($data['image']) && $data['image'] instanceof UploadedFile) {
                $data['image'] = fileUploader('courses/', 'png', $data['image']);
            }

            if (! empty($data['class_routine_image']) && $data['class_routine_image'] instanceof UploadedFile) {
                $data['class_routine_image'] = fileUploader('courses/', 'png', $data['class_routine_image']);
            }

            if (! empty($data['uploaded_video_path']) && $data['uploaded_video_path'] instanceof UploadedFile) {
                $data['uploaded_video_path'] = fileUploader('courses/', 'mp4', $data['uploaded_video_path']);
            }
        } else {
            $data['slug'] = $this->createUniqueSlug($data['title'], $this->table, 'slug', '-');

            if (! empty($data['image']) && $data['image'] instanceof UploadedFile) {
                $data['image'] = fileUploader('courses/', 'png', $data['image'], $item->image);
            }

            if (! empty($data['class_routine_image']) && $data['class_routine_image'] instanceof UploadedFile) {
                $data['class_routine_image'] = fileUploader('courses/', 'png', $data['class_routine_image'], $item->class_routine_image);
            }

            if (! empty($data['uploaded_video_path']) && $data['uploaded_video_path'] instanceof UploadedFile) {
                $data['uploaded_video_path'] = fileUploader('courses/', 'mp4', $data['uploaded_video_path'], $item->uploaded_video_path);
            }
            $data['updated_at'] = now();
        }

        return $data;
    }

    /**
     * @throws Exception
     */
    public function update(int $id, array $data): ?object
    {
        try {
            $user = Course::find($id);
            $data = $this->prepareForDB($data, $user);
            parent::update($id, $data);

            return $this->getById($id);
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * True when the course has related chapters, enrollments, or zoom meetings —
     * the same "cannot delete, related data exists" rule the Admin course
     * controller already enforces (Modules\Elearning\Http\Controllers\API\
     * CourseController::destroy). Extracted here so the Teacher course
     * controller can reuse the exact same rule instead of re-deciding it.
     */
    public function hasProtectedRelations(int $id): bool
    {
        $course = Course::with(['courseChapters', 'enrollments', 'zoomMeetings'])->find($id);

        if (! $course) {
            return false;
        }

        return $course->courseChapters->isNotEmpty()
            || $course->enrollments->isNotEmpty()
            || $course->zoomMeetings->isNotEmpty();
    }

    public function courseStatusUpdate(array $data): ?object
    {
        try {
            $course = Course::find($data['course_id']);
            $course->status = $data['status'];
            $course->save();

            return $course;
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    protected function getExceptionMessages(): array
    {
        $exceptionMessages = parent::getExceptionMessages();

        $userExceptionMessages = [
            static::MESSAGE_ITEM_DOES_NOT_EXIST_MESSAGE => 'Course does not exist.',
            static::MESSAGE_ITEM_COULD_NOT_BE_DELETED => 'Course could not be deleted.',
        ];

        return array_merge($exceptionMessages, $userExceptionMessages);
    }
}
