<?php

namespace Modules\Elearning\Repositories;

use App\Abstracts\EntityRepository;
use App\Traits\SlugAbleTrait;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\UploadedFile;
use Modules\Elearning\Models\CourseCategory;
use Symfony\Component\HttpFoundation\Response;

class CourseCategoryRepository extends EntityRepository
{
    use SlugAbleTrait;

    public string $table = CourseCategory::TABLE_NAME;

    protected array $fillableColumns = [
        'institute_id',
        'name',
        'slug',
        'image',
        'bg_color',
        'priority',
        'enable_homepage',
        'description',
        'parent_id',
        'status',
        'created_at',
        'updated_at',
    ];

    protected function getQuery(): Builder
    {
        return parent::getQuery();
    }

    public function getAll(array $filterData = []): Paginator
    {
        $filter = $this->getFilterData($filterData);
        $query = $this->getCourseCategoryQuery();

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

    private function getCourseCategoryQuery(): Builder
    {
        return $this->getQuery()
            ->select(
                'course_categories.id',
                'course_categories.institute_id',
                'course_categories.name',
                'course_categories.slug',
                'course_categories.image',
                'course_categories.bg_color',
                'course_categories.priority',
                'course_categories.enable_homepage',
                'course_categories.description',
                'course_categories.parent_id',
                'course_categories.status',
                'course_categories.created_at',
                'course_categories.deleted_at',
            );
    }

    protected function filterSearchQuery(Builder|EloquentBuilder $query, string $searchedText): Builder
    {
        $searchable = "%$searchedText%";

        return $query->where('course_categories.name', 'LIKE', $searchable)
            ->orWhere('course_categories.slug', 'LIKE', $searchable)
            ->orWhere('course_categories.priority', 'LIKE', $searchable)
            ->orWhere('course_categories.status', 'LIKE', $searchable);
    }

    /**
     * @throws Exception
     */
    public function getByColumn(string $columnName, $columnValue, array $selects = ['*']): ?object
    {
        $user = $this->getCourseCategoryQuery()
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
            $userId = $this->getQuery()->insertGetId($data);
            $user = CourseCategory::find($userId);

            return $user;
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
            $data['created_at'] = now();
            $data['created_by'] = $this->getCurrentUserId();
            $data['institute_id'] = $this->getCurrentInstituteId();
            $data['status'] = 1;
            $data['slug'] = $this->createUniqueSlug($data['name'], $this->table, 'slug', '-');
            if (! empty($data['image']) && $data['image'] instanceof UploadedFile) {
                $data['image'] = fileUploader('course_categories/', 'png', $data['image']);
            }
        } else {
            $data['slug'] = $this->createUniqueSlug($data['name'], $this->table, 'slug', '-');
            if (! empty($data['image']) && $data['image'] instanceof UploadedFile) {
                $data['image'] = fileUploader('course_categories/', 'png', $data['image'], $item->image);
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
            $user = CourseCategory::find($id);
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
            static::MESSAGE_ITEM_DOES_NOT_EXIST_MESSAGE => 'Course Category does not exist.',
            static::MESSAGE_ITEM_COULD_NOT_BE_DELETED => 'Course Category could not be deleted.',
        ];

        return array_merge($exceptionMessages, $userExceptionMessages);
    }
}
