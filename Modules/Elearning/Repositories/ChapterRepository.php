<?php

namespace Modules\Elearning\Repositories;

use App\Abstracts\EntityRepository;
use App\Traits\SlugAbleTrait;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\UploadedFile;
use Modules\Elearning\Models\Chapter;
use Symfony\Component\HttpFoundation\Response;

class ChapterRepository extends EntityRepository
{
    use SlugAbleTrait;

    public string $table = Chapter::TABLE_NAME;

    protected array $fillableColumns = [
        'institute_id',
        'course_id',
        'title',
        'slug',
        'priority',
        'image',
        'description',
        'meta_description',
        'meta_keywords',
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
        $query = $this->getChapterQuery();

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

    private function getChapterQuery(): Builder
    {
        return $this->getQuery()
            ->leftJoin('courses', 'chapters.course_id', '=', 'courses.id')
            ->leftJoin('users as user_created_by', 'chapters.created_by', '=', 'user_created_by.id')
            ->select(
                'chapters.id',
                'chapters.institute_id',
                'chapters.course_id',
                'courses.title as course_title',
                'chapters.title',
                'chapters.slug',
                'chapters.priority',
                'chapters.image',
                'chapters.description',
                'chapters.meta_description',
                'chapters.meta_keywords',
                'chapters.created_by',
                'user_created_by.name as created_by_name',
                'chapters.status',
                'chapters.deleted_at'
            );
    }

    protected function filterSearchQuery(Builder|EloquentBuilder $query, string $searchedText): Builder
    {
        $searchable = "%$searchedText%";

        return $query->where('chapters.title', 'LIKE', $searchable)
            ->orWhere('chapters.course_id', 'LIKE', $searchable)
            ->orWhere('chapters.title', 'LIKE', $searchable)
            ->orWhere('chapters.slug', 'LIKE', $searchable)
            ->orWhere('chapters.status', 'LIKE', $searchable);
    }

    /**
     * @throws Exception
     */
    public function getByColumn(string $columnName, $columnValue, array $selects = ['*']): ?object
    {
        $chapter = $this->getChapterQuery()
            ->where($columnName, $columnValue)
            ->first();

        if (empty($chapter)) {
            throw new Exception(
                $this->getExceptionMessage(static::MESSAGE_ITEM_DOES_NOT_EXIST_MESSAGE),
                Response::HTTP_NOT_FOUND
            );
        }

        return $chapter;
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
            $chapterId = $this->getQuery()->insertGetId($data);
            $chapter = Chapter::find($chapterId);
            $chapter->update([
                'priority' => $chapterId,
            ]);

            return $chapter;
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
            $data['status'] = 'active';
            $data['created_at'] = now();
            $data['created_by'] = $this->getCurrentUserId();
            $data['institute_id'] = $this->getCurrentInstituteId();
            $data['slug'] = $this->createUniqueSlug($data['title'], $this->table, 'slug', '-');
            if (! empty($data['image']) && $data['image'] instanceof UploadedFile) {
                $data['image'] = fileUploader('chapters/', 'png', $data['image']);
            }
        } else {
            $data['slug'] = $this->createUniqueSlug($data['title'], $this->table, 'slug', '-');
            if (! empty($data['image']) && $data['image'] instanceof UploadedFile) {
                $data['image'] = fileUploader('chapters/', 'png', $data['image'], $item->image);
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
            $chapter = Chapter::find($id);
            $data = $this->prepareForDB($data, $chapter);
            parent::update($id, $data);

            return $this->getById($id);
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    protected function getExceptionMessages(): array
    {
        $exceptionMessages = parent::getExceptionMessages();

        $chapterExceptionMessages = [
            static::MESSAGE_ITEM_DOES_NOT_EXIST_MESSAGE => 'Chapter does not exist.',
            static::MESSAGE_ITEM_COULD_NOT_BE_DELETED => 'Chapter could not be deleted.',
        ];

        return array_merge($exceptionMessages, $chapterExceptionMessages);
    }
}
