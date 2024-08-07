<?php

namespace Modules\QuestionBank\Repositories;

use App\Abstracts\EntityRepository;
use App\Traits\SlugAbleTrait;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\UploadedFile;
use Modules\QuestionBank\Models\QuestionCategory;
use Symfony\Component\HttpFoundation\Response;

class QuestionCategoryRepository extends EntityRepository
{
    use SlugAbleTrait;

    public string $table = QuestionCategory::TABLE_NAME;

    protected array $fillableColumns = [
        'institute_id',
        'name',
        'priority',
        'slug',
        'image',
        'icon',
        'color_code',
        'status',
        'created_by',
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
        $query = $this->getQuestionCategoryQuery();

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

    private function getQuestionCategoryQuery(): Builder
    {
        return $this->getQuery()
            ->select(
                'question_categories.id',
                'question_categories.institute_id',
                'question_categories.name',
                'question_categories.color_code',
                'question_categories.priority',
                'question_categories.slug',
                'question_categories.image',
                'question_categories.icon',
                'question_categories.status',
                'question_categories.created_by',
                'question_categories.created_at',
                'question_categories.deleted_at',
            );
    }

    protected function filterSearchQuery(Builder|EloquentBuilder $query, string $searchedText): Builder
    {
        $searchable = "%$searchedText%";

        return $query->where('question_categories.name', 'LIKE', $searchable)
            ->orWhere('question_categories.priority', 'LIKE', $searchable)
            ->orWhere('question_categories.status', 'LIKE', $searchable);
    }

    /**
     * @throws Exception
     */
    public function getByColumn(string $columnName, $columnValue, array $selects = ['*']): ?object
    {
        $user = $this->getQuestionCategoryQuery()
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
            $user = QuestionCategory::find($userId);

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

            $data['slug'] = $this->createUniqueSlug($data['name'], 'question_categories', 'slug', '-');
            if (! empty($data['image']) && $data['image'] instanceof UploadedFile) {
                $data['image'] = fileUploader('question_categories/', 'png', $data['image']);
            }
            if (! empty($data['icon']) && $data['icon'] instanceof UploadedFile) {
                $data['icon'] = fileUploader('question_categories/', 'png', $data['icon']);
            }
        } else {
            $data['slug'] = $this->createUniqueSlug($data['name'], 'question_categories', 'slug', '-');
            if (! empty($data['image']) && $data['image'] instanceof UploadedFile) {
                $data['image'] = fileUploader('question_categories/', 'png', $data['image'], $item->image);
            }
            if (! empty($data['icon']) && $data['icon'] instanceof UploadedFile) {
                $data['icon'] = fileUploader('question_categories/', 'png', $data['icon'], $item->icon);
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
            $user = QuestionCategory::find($id);
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
            static::MESSAGE_ITEM_DOES_NOT_EXIST_MESSAGE => 'Question Category does not exist.',
            static::MESSAGE_ITEM_COULD_NOT_BE_DELETED => 'Question Category could not be deleted.',
        ];

        return array_merge($exceptionMessages, $userExceptionMessages);
    }
}
