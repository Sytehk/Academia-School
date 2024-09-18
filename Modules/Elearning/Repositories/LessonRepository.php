<?php

namespace Modules\Elearning\Repositories;

use App\Abstracts\EntityRepository;
use App\Traits\SlugAbleTrait;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\UploadedFile;
use Modules\Elearning\Models\Content;
use Modules\Elearning\Models\Lesson;
use Symfony\Component\HttpFoundation\Response;

class LessonRepository extends EntityRepository
{
    use SlugAbleTrait;

    public string $table = Lesson::TABLE_NAME;

    protected array $fillableColumns = [
        'institute_id',
        'course_id',
        'chapter_id',
        'title',
        'slug',
        'description',
        'thumbnail_image',

        // Video Handling
        'video_type',
        'video_url',
        'embedded_url',
        'uploaded_video_path',
        'document_file',
        'attachments',
        'priority',

        // Playback
        'playback_hours',
        'playback_minutes',
        'playback_seconds',

        // Scheduling
        'is_scheduled',
        'scheduled_at',

        // Visibility
        'visibility',
        'password',

        // Status & Meta
        'status',
        'created_by',
    ];

    protected function getQuery(): Builder
    {
        return parent::getQuery();
    }

    public function getAll(array $filterData = []): Paginator
    {
        $filter = $this->getFilterData($filterData);
        $query = $this->getLessonQuery();

        if (! $filter['with_deleted']) {
            $query->whereNull("{$this->table}.deleted_at");
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
        ];

        return array_merge($defaultArgs, $filterData);
    }

    private function getLessonQuery(): Builder
    {
        return $this->getQuery()
            ->leftJoin('users as user_created_by', 'lessons.created_by', '=', 'user_created_by.id')
            ->select(
                'lessons.id',
                'lessons.institute_id',
                'lessons.course_id',
                'lessons.chapter_id',
                'lessons.title',
                'lessons.slug',
                'lessons.description',
                'lessons.thumbnail_image',
                'lessons.video_type',
                'lessons.video_url',
                'lessons.embedded_url',
                'lessons.uploaded_video_path',
                'lessons.document_file',
                'lessons.attachments',
                'lessons.priority',
                'lessons.playback_hours',
                'lessons.playback_minutes',
                'lessons.playback_seconds',
                'lessons.is_scheduled',
                'lessons.scheduled_at',
                'lessons.visibility',
                'lessons.password',
                'lessons.status',
                'lessons.created_by',
                'user_created_by.name as created_by_name',
                'lessons.deleted_at'
            );
    }

    protected function filterSearchQuery(Builder|EloquentBuilder $query, string $searchedText): Builder
    {
        $searchable = "%$searchedText%";

        return $query->where('lessons.title', 'LIKE', $searchable)
            ->orWhere('lessons.course_id', 'LIKE', $searchable)
            ->orWhere('lessons.title', 'LIKE', $searchable)
            ->orWhere('lessons.slug', 'LIKE', $searchable)
            ->orWhere('lessons.status', 'LIKE', $searchable);
    }

    /**
     * @throws Exception
     */
    public function getByColumn(string $columnName, $columnValue, array $selects = ['*']): ?object
    {
        $user = $this->getLessonQuery()
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
        try {
            $data = $this->prepareForDB($data);
            $lessonId = $this->getQuery()->insertGetId($data);
            $lesson = Lesson::find($lessonId);

            $maxSerial = Content::max('serial');
            Content::create([
                'institute_id' => $this->getCurrentInstituteId(),
                'chapter_id' => $lesson->chapter_id,
                'type' => 'lesson',
                'type_id' => $lessonId,
                'serial' => ($maxSerial ?? 0) + 1,
            ]);

            return $lesson;
        } catch (Exception $exception) {
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
            $data['slug'] = $this->createUniqueSlug($data['title'], $this->table, 'slug', '-');
            $data['status'] = 'active';
            $data['created_at'] = now();
            $data['created_by'] = $this->getCurrentUserId();
            $data['institute_id'] = $this->getCurrentInstituteId();
            if (! empty($data['thumbnail_image']) && $data['thumbnail_image'] instanceof UploadedFile) {
                $data['thumbnail_image'] = fileUploader('lessons/', 'png', $data['thumbnail_image']);
            }

            if (! empty($data['uploaded_video_path']) && $data['uploaded_video_path'] instanceof UploadedFile) {
                $data['uploaded_video_path'] = fileUploader('lessons/', 'mp4', $data['uploaded_video_path']);
            }

            if (! empty($data['document_file']) && $data['document_file'] instanceof UploadedFile) {
                $data['document_file'] = fileUploader('lessons/document_file/', 'pdf', $data['document_file']);
            }
        } else {
            $data['slug'] = $this->createUniqueSlug($data['title'], $this->table, 'slug', '-');
            if (! empty($data['thumbnail_image']) && $data['thumbnail_image'] instanceof UploadedFile) {
                $data['thumbnail_image'] = fileUploader('lessons/', 'png', $data['thumbnail_image'], $item->thumbnail_image);
            }

            if (! empty($data['uploaded_video_path']) && $data['uploaded_video_path'] instanceof UploadedFile) {
                $data['uploaded_video_path'] = fileUploader('lessons/', 'mp4', $data['uploaded_video_path'], $item->uploaded_video_path);
            }

            if (! empty($data['document_file']) && $data['document_file'] instanceof UploadedFile) {
                $data['document_file'] = fileUploader('lessons/document_file/', 'pdf', $data['document_file']);
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
            $user = Lesson::find($id);
            $data = $this->prepareForDB($data, $user);
            parent::update($id, $data);

            return $this->getById($id);
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    protected function getExceptionMessages(): array
    {
        $exceptionMessages = parent::getExceptionMessages();

        $userExceptionMessages = [
            static::MESSAGE_ITEM_DOES_NOT_EXIST_MESSAGE => 'Lesson does not exist.',
            static::MESSAGE_ITEM_COULD_NOT_BE_DELETED => 'Lesson could not be deleted.',
        ];

        return array_merge($exceptionMessages, $userExceptionMessages);
    }
}
