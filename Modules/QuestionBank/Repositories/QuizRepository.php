<?php

namespace Modules\QuestionBank\Repositories;

use App\Abstracts\EntityRepository;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Modules\QuestionBank\Models\Quiz;
use Symfony\Component\HttpFoundation\Response;

class QuizRepository extends EntityRepository
{
    public string $table = Quiz::TABLE_NAME;

    protected array $fillableColumns = [
        'institute_id',
        'branch_id',
        'created_by',
        'title',
        'description',
        'guidelines',
        'show_description_on_course_page',
        'type',
        'question_ids',
        'start_time',
        'end_time',
        'has_time_limit',
        'time_limit_value',
        'time_limit_unit',
        'on_expiry',
        'marks_per_question',
        'negative_marks_per_wrong_answer',
        'pass_mark',
        'attempts_allowed',
        'enable_negative_marking',
        'result_visibility',
        'layout_pages',
        'shuffle_questions',
        'shuffle_options',
        'access_type',
        'access_password',
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
        $query = $this->getQuizQuery();

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

    private function getQuizQuery(): EloquentBuilder
    {
        return Quiz::query()
            ->leftJoin('users as created_by_user', 'quizzes.created_by', '=', 'created_by_user.id')
            ->leftJoin('institutes', 'quizzes.institute_id', '=', 'institutes.id')
            ->select([
                'quizzes.id',
                'quizzes.institute_id',
                'institutes.name as institute_name',
                'quizzes.branch_id',
                'quizzes.title',
                'quizzes.description',
                'quizzes.guidelines',
                'quizzes.show_description_on_course_page',
                'quizzes.type',
                'quizzes.question_ids',
                'quizzes.start_time',
                'quizzes.end_time',
                'quizzes.has_time_limit',
                'quizzes.time_limit_value',
                'quizzes.time_limit_unit',
                'quizzes.on_expiry',
                'quizzes.marks_per_question',
                'quizzes.negative_marks_per_wrong_answer',
                'quizzes.pass_mark',
                'quizzes.attempts_allowed',
                'quizzes.enable_negative_marking',
                'quizzes.result_visibility',
                'quizzes.layout_pages',
                'quizzes.shuffle_questions',
                'quizzes.shuffle_options',
                'quizzes.access_type',
                'quizzes.access_password',
                'quizzes.status',

                'quizzes.created_by',
                'created_by_user.name as created_by_name',
                'created_by_user.email as created_by_email',

                'quizzes.created_at',
                'quizzes.updated_at',
                'quizzes.deleted_at',
            ]);
    }

    protected function filterSearchQuery(Builder|EloquentBuilder $query, string $searchedText): Builder
    {
        $searchable = "%$searchedText%";

        return $query->where('quizzes.title', 'LIKE', $searchable)
            ->orWhere('quizzes.status', 'LIKE', $searchable);
    }

    /**
     * @throws Exception
     */
    public function getByColumn(string $columnName, $columnValue, array $selects = ['*']): ?object
    {
        $user = $this->getQuizQuery()
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
            $quizId = $this->getQuery()->insertGetId($data);
            $quiz = Quiz::find($quizId);

            return $quiz;
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
            $data['branch_id'] = $this->getCurrentBranchId();

            $data['result_visibility'] = $data['result_visibility'] ?? 'after_review';
            if (
                ($data['has_time_limit'] ?? false) === false &&
                $data['result_visibility'] === 'after_review'
            ) {
                $data['type'] = 'practice';
            } else {
                $data['type'] = 'exam';
            }
        } else {
            $data['result_visibility'] = $data['result_visibility'] ?? 'after_review';
            if (
                ($data['has_time_limit'] ?? false) === false &&
                $data['result_visibility'] === 'after_review'
            ) {
                $data['type'] = 'practice';
            } else {
                $data['type'] = 'exam';
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
            $user = Quiz::find($id);
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
            static::MESSAGE_ITEM_DOES_NOT_EXIST_MESSAGE => 'Quiz does not exist.',
            static::MESSAGE_ITEM_COULD_NOT_BE_DELETED => 'Quiz could not be deleted.',
        ];

        return array_merge($exceptionMessages, $userExceptionMessages);
    }
}
