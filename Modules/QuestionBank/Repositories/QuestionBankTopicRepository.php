<?php

namespace Modules\QuestionBank\Repositories;

use App\Abstracts\EntityRepository;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Modules\QuestionBank\Models\QuestionBankTopic;
use Symfony\Component\HttpFoundation\Response;

class QuestionBankTopicRepository extends EntityRepository
{
    public string $table = QuestionBankTopic::TABLE_NAME;

    protected array $fillableColumns = [
        'name',
        'question_bank_chapter_id',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected function getQuery(): Builder
    {
        return parent::getQuery();
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

    private function getQuestionBankTopicQuery(): Builder
    {
        return $this->getQuery()
            ->leftJoin('question_bank_chapters', 'question_bank_chapters.id', '=', 'question_bank_topics.question_bank_chapter_id')
            ->select(
                'question_bank_topics.id',
                'question_bank_topics.question_bank_chapter_id',
                'question_bank_chapters.name as chapter_name',
                'question_bank_topics.name',
                'question_bank_topics.status',
                'question_bank_topics.created_at',
                'question_bank_topics.deleted_at',
            );
    }

    protected function filterSearchQuery(Builder|EloquentBuilder $query, string $searchedText): Builder
    {
        $searchable = "%$searchedText%";

        return $query->where("{$this->table}.question_bank_chapter_id", 'LIKE', $searchable)
            ->orWhere("{$this->table}.name", 'LIKE', $searchable);
    }

    public function getAll(array $filterData = []): Paginator
    {
        $filter = $this->getFilterData($filterData);
        $query = $this->getQuestionBankTopicQuery();

        if (! $filter['with_deleted']) {
            $query->whereNull("{$this->table}.deleted_at");
        }

        if (! empty($filter['search'])) {
            $query = $this->filterSearchQuery($query, $filter['search']);
        }

        // Proper null checking for question_bank_chapter_id
        if (isset($filter['question_bank_chapter_id']) && $filter['question_bank_chapter_id'] !== null && $filter['question_bank_chapter_id'] !== 'null') {
            $query->where("{$this->table}.question_bank_chapter_id", (int) $filter['question_bank_chapter_id']);
        }

        return $query
            ->orderBy($filter['orderBy'], $filter['order'])
            ->paginate($filter['perPage']);
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
        $item = $this->getQuestionBankTopicQuery()
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

        return QuestionBankTopic::find($id);
    }

    /**
     * @throws Exception
     */
    public function update(int $id, array $data): object
    {
        $item = QuestionBankTopic::findOrFail($id);
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
        } else {
            $data['updated_at'] = now();
        }

        return $data;
    }

    protected function getExceptionMessages(): array
    {
        return [
            static::MESSAGE_ITEM_DOES_NOT_EXIST_MESSAGE => 'QuestionBankTopic does not exist.',
            static::MESSAGE_ITEM_COULD_NOT_BE_DELETED => 'QuestionBankTopic could not be deleted.',
        ];
    }
}
