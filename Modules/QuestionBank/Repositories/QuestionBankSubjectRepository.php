<?php

namespace Modules\QuestionBank\Repositories;

use App\Abstracts\EntityRepository;
use App\Traits\SlugAbleTrait;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\UploadedFile;
use Modules\QuestionBank\Models\QuestionBankSubject;
use Symfony\Component\HttpFoundation\Response;

class QuestionBankSubjectRepository extends EntityRepository
{
    use SlugAbleTrait;

    public string $table = QuestionBankSubject::TABLE_NAME;

    protected array $fillableColumns = [
        'name',
        'question_category_id',
        'class_id',
        'group_id',
        'slug',
        'code',
        'type',
        'color_code',
        'priority',
        'description',
        'icon',
        'image',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected function getQuery(): Builder
    {
        return parent::getQuery();
    }

    public function getAll(array $filterData = []): Paginator
    {
        $filter = $this->getFilterData($filterData);
        $query = $this->getQuestionBankSubjectQuery();

        if (! $filter['with_deleted']) {
            $query->whereNull("{$this->table}.deleted_at");
        }

        if (! empty($filter['search'])) {
            $query = $this->filterSearchQuery($query, $filter['search']);
        }

        // Proper null checking for class_id
        if (isset($filter['class_id']) && $filter['class_id'] !== null && $filter['class_id'] !== 'null') {
            $query->where("{$this->table}.class_id", (int) $filter['class_id']);
        }

        // Proper null checking for group_id
        if (isset($filter['group_id']) && $filter['group_id'] !== null && $filter['group_id'] !== 'null') {
            $query->where("{$this->table}.group_id", (int) $filter['group_id']);
        }

        return $query
            ->orderBy($filter['orderBy'], $filter['order'])
            ->paginate($filter['perPage']);
    }

    private function getQuestionBankSubjectQuery(): Builder
    {
        return $this->getQuery()
            ->leftJoin('question_bank_classes', 'question_bank_classes.id', '=', 'question_bank_subjects.class_id')
            ->leftJoin('question_bank_groups', 'question_bank_groups.id', '=', 'question_bank_subjects.group_id')
            ->leftJoin('question_categories', 'question_categories.id', '=', 'question_bank_subjects.question_category_id')
            ->select(
                'question_bank_subjects.id',
                'question_bank_subjects.class_id',
                'question_bank_classes.name as class_name',
                'question_bank_subjects.group_id',
                'question_bank_groups.name as group_name',
                'question_bank_subjects.question_category_id',
                'question_categories.name as category_name',
                'question_bank_subjects.name',
                'question_bank_subjects.slug',
                'question_bank_subjects.code',
                'question_bank_subjects.type',
                'question_bank_subjects.color_code',
                'question_bank_subjects.priority',
                'question_bank_subjects.description',
                'question_bank_subjects.icon',
                'question_bank_subjects.image',
                'question_bank_subjects.status',
                'question_bank_subjects.created_at',
                'question_bank_subjects.deleted_at',
            );
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

    protected function filterSearchQuery(Builder|EloquentBuilder $query, string $searchedText): Builder
    {
        $searchable = "%$searchedText%";

        return $query->where("{$this->table}.name", 'LIKE', $searchable)
            ->orWhere("{$this->table}.status", 'LIKE', $searchable);
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
    public function getByColumn(string $columnName, $columnValue, array $selects = ['*']): ?object
    {
        $item = $this->getQuestionBankSubjectQuery()
            ->where($columnName, $columnValue)
            ->first($selects);

        if (empty($item)) {
            throw new Exception(
                $this->getExceptionMessage(static::MESSAGE_ITEM_DOES_NOT_EXIST_MESSAGE),
                Response::HTTP_NOT_FOUND
            );
        }

        return $item;
    }

    /**
     * @throws Exception
     */
    public function create(array $data): object
    {
        $data = $this->prepareForDB($data);
        $id = $this->getQuery()->insertGetId($data);

        return QuestionBankSubject::find($id);
    }

    /**
     * @throws Exception
     */
    public function update(int $id, array $data): object
    {
        $item = QuestionBankSubject::findOrFail($id);
        $data = $this->prepareForDB($data, $item);
        parent::update($id, $data);

        return $this->getById($id);
    }

    /**
     * @throws Exception
     */
    public function prepareForDB(array $data, ?object $item = null): array
    {
        $data = parent::prepareForDB($data, $item);
        if (empty($item)) {
            $data['created_at'] = now();
            $data['status'] = 1;

            $data['slug'] = $this->createUniqueSlug($data['name'], 'question_bank_subjects', 'slug', '-');
            if (! empty($data['image']) && $data['image'] instanceof UploadedFile) {
                $data['image'] = fileUploader('question_bank_subjects/', 'png', $data['image']);
            }
            if (! empty($data['icon']) && $data['icon'] instanceof UploadedFile) {
                $data['icon'] = fileUploader('question_bank_subjects/', 'png', $data['icon']);
            }
        } else {
            $data['slug'] = $this->createUniqueSlug($data['name'], 'question_bank_subjects', 'slug', '-');
            if (! empty($data['image']) && $data['image'] instanceof UploadedFile) {
                $data['image'] = fileUploader('question_bank_subjects/', 'png', $data['image'], $item->image);
            }
            if (! empty($data['icon']) && $data['icon'] instanceof UploadedFile) {
                $data['icon'] = fileUploader('question_bank_subjects/', 'png', $data['icon'], $item->icon);
            }
            $data['updated_at'] = now();
        }

        return $data;
    }

    protected function getExceptionMessages(): array
    {
        return [
            static::MESSAGE_ITEM_DOES_NOT_EXIST_MESSAGE => 'QuestionBankSubject does not exist.',
            static::MESSAGE_ITEM_COULD_NOT_BE_DELETED => 'QuestionBankSubject could not be deleted.',
        ];
    }
}
