<?php

namespace Modules\QuestionBank\Repositories;

use App\Abstracts\EntityRepository;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Modules\QuestionBank\Models\Question;
use Symfony\Component\HttpFoundation\Response;

class QuestionRepository extends EntityRepository
{
    public string $table = Question::TABLE_NAME;

    protected array $fillableColumns = [
        'institute_id',
        'question_bank_class_id',
        'question_bank_group_id',
        'question_bank_subject_id',
        'question_bank_chapter_id',
        'question_category_id',
        'type',
        'question_name',
        'question',
        'options',
        'correct_answer',
        'explanation',
        'marks',
        'negative_marks',
        'negative_marks_type',
        'image_file',
        'price',
        'question_year',
        'language',
        'status',
        'created_by',
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
        $query = $this->getQuestionQuery();

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

    private function getQuestionQuery(): Builder
    {
        return $this->getQuery()
            ->leftJoin('users as user_created_by', 'questions.created_by', '=', 'user_created_by.id')
            ->leftJoin('question_categories as category', 'questions.question_category_id', '=', 'category.id')
            ->select(
                'questions.id',
                'questions.institute_id',
                'questions.question_category_id',
                'category.name as question_category_name', // Get category name
                'questions.question',
                'questions.options',
                'questions.type',
                'questions.correct_answer',
                'questions.marks',
                'questions.negative_marks',
                'questions.status',
                'questions.created_by',
                'questions.deleted_at'
            );
    }

    protected function filterSearchQuery(Builder|EloquentBuilder $query, string $searchedText): Builder
    {
        $searchable = "%$searchedText%";

        return $query->where('questions.question', 'LIKE', $searchable)
            ->orWhere('questions.type', 'LIKE', $searchable)
            ->orWhere('questions.marks', 'LIKE', $searchable)
            ->orWhere('questions.status', 'LIKE', $searchable);
    }

    /**
     * @throws Exception
     */
    public function getByColumn(string $columnName, $columnValue, array $selects = ['*']): ?object
    {
        $user = $this->getQuestionQuery()
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
            $user = Question::find($userId);

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
            $user = Question::find($id);
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
            static::MESSAGE_ITEM_DOES_NOT_EXIST_MESSAGE => 'Question does not exist.',
            static::MESSAGE_ITEM_COULD_NOT_BE_DELETED => 'Question could not be deleted.',
        ];

        return array_merge($exceptionMessages, $userExceptionMessages);
    }
}
