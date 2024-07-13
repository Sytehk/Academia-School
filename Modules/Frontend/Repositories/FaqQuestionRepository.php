<?php

namespace Modules\Frontend\Repositories;

use App\Abstracts\EntityRepository;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Modules\Frontend\Models\FaqQuestion;
use Symfony\Component\HttpFoundation\Response;

class FaqQuestionRepository extends EntityRepository
{
    public string $table = FaqQuestion::TABLE_NAME;

    protected array $fillableColumns = [
        'institute_id',
        'question',
        'answer',
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
        $query = $this->getFaqQuestionQuery();

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

    private function getFaqQuestionQuery(): Builder
    {
        return $this->getQuery()
            ->select(
                'faq_questions.id',
                'faq_questions.institute_id',
                'faq_questions.question',
                'faq_questions.answer',
                'faq_questions.status',
                'faq_questions.created_at'
            );
    }

    protected function filterSearchQuery(Builder|EloquentBuilder $query, string $searchedText): Builder
    {
        $searchable = "%$searchedText%";

        return $query->where('faq_questions.question', 'LIKE', $searchable)
            ->orWhere('faq_questions.answer', 'LIKE', $searchable)
            ->orWhere('faq_questions.status', 'LIKE', $searchable);
    }

    /**
     * @throws Exception
     */
    public function getByColumn(string $columnName, $columnValue, array $selects = ['*']): ?object
    {
        $user = $this->getFaqQuestionQuery()
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
            $user = FaqQuestion::find($userId);

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
            $data['institute_id'] = $this->getCurrentInstituteId();
            $data['branch_id'] = $this->getCurrentBranchId();
            $data['status'] = 1;
        } else {
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
            $user = FaqQuestion::find($id);
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
            static::MESSAGE_ITEM_DOES_NOT_EXIST_MESSAGE => 'FaqQuestion does not exist.',
            static::MESSAGE_ITEM_COULD_NOT_BE_DELETED => 'FaqQuestion could not be deleted.',
        ];

        return array_merge($exceptionMessages, $userExceptionMessages);
    }
}
