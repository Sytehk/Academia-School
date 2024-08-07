<?php

namespace Modules\QuestionBank\Repositories;

use App\Abstracts\EntityRepository;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Modules\QuestionBank\Models\QuestionBankSubSource;
use Symfony\Component\HttpFoundation\Response;

class QuestionBankSubSourceRepository extends EntityRepository
{
    public string $table = QuestionBankSubSource::TABLE_NAME;

    protected array $fillableColumns = [
        'source_id',
        'sub_source_name',
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

    private function getQuestionBankSubSourceQuery(): Builder
    {
        return $this->getQuery()
            ->join('question_bank_sources', 'question_bank_sources.id', '=', 'question_bank_sub_sources.source_id')
            ->select(
                'question_bank_sub_sources.id',
                'question_bank_sub_sources.source_id',
                'question_bank_sources.source_name as source_name',
                'question_bank_sub_sources.sub_source_name',
                'question_bank_sub_sources.status',
                'question_bank_sub_sources.created_at',
                'question_bank_sub_sources.deleted_at',
            );
    }

    protected function filterSearchQuery(Builder|EloquentBuilder $query, string $searchedText): Builder
    {
        $searchable = "%$searchedText%";

        return $query->where("{$this->table}.source_id", 'LIKE', $searchable)
            ->orWhere("{$this->table}.sub_source_name", 'LIKE', $searchable);
    }

    public function getAll(array $filterData = []): Paginator
    {
        $filter = $this->getFilterData(filterData: $filterData);
        $query = $this->getQuestionBankSubSourceQuery();

        if (! $filter['with_deleted']) {
            $query->whereNull("{$this->table}.deleted_at");
        }

        if (! empty($filter['search'])) {
            $query = $this->filterSearchQuery($query, $filter['search']);
        }

        // Proper null checking for source_id
        if (isset($filter['source_id']) && $filter['source_id'] !== null && $filter['source_id'] !== 'null') {
            $query->where("{$this->table}.source_id", (int) $filter['source_id']);
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
        $item = $this->getQuestionBankSubSourceQuery()
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

        return QuestionBankSubSource::find($id);
    }

    /**
     * @throws Exception
     */
    public function update(int $id, array $data): object
    {
        $item = QuestionBankSubSource::findOrFail($id);
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
            static::MESSAGE_ITEM_DOES_NOT_EXIST_MESSAGE => 'QuestionBankSubSource does not exist.',
            static::MESSAGE_ITEM_COULD_NOT_BE_DELETED => 'QuestionBankSubSource could not be deleted.',
        ];
    }
}
