<?php

namespace Modules\Elearning\Repositories;

use App\Abstracts\EntityRepository;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Modules\Elearning\Models\ClassRoutine;
use Symfony\Component\HttpFoundation\Response;

class ClassRoutineRepository extends EntityRepository
{
    public string $table = ClassRoutine::TABLE_NAME;

    protected array $fillableColumns = [
        'institute_id',
        'course_id',
        'teacher_id',
        'room_id',
        'start_time',
        'end_time',
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

    private function getClassRoutineQuery(): EloquentBuilder
    {
        return ClassRoutine::with([
            'course',
            'teacher',
            'room',
            'days',
        ])
            ->select([
                'id',
                'institute_id',
                'course_id',
                'teacher_id',
                'room_id',
                'start_time',
                'end_time',
                'status',
                'created_at',
                'deleted_at',
            ]);
    }

    protected function filterSearchQuery(Builder|EloquentBuilder $query, string $searchedText): Builder
    {
        $searchable = "%$searchedText%";

        return $query->where("{$this->table}.name", 'LIKE', $searchable)
            ->orWhere("{$this->table}.status", 'LIKE', $searchable);
    }

    public function getAll(array $filterData = []): Paginator
    {
        $filter = $this->getFilterData($filterData);
        $query = $this->getClassRoutineQuery()->select('*');

        if (! $filter['with_deleted']) {
            $query->whereNull("{$this->table}.deleted_at");
        }

        if (! empty($filter['search'])) {
            $query = $this->filterSearchQuery($query, $filter['search']);
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
        $item = $this->getClassRoutineQuery()
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
        $days = $data['days'] ?? []; // capture days and remove from data array
        unset($data['days']);

        $data = $this->prepareForDB($data);

        // Insert and get the ID
        $id = $this->getQuery()->insertGetId($data);

        // Get the created model
        $routine = ClassRoutine::findOrFail($id);

        // Sync the days (if any)
        if (! empty($days)) {
            $routine->days()->sync($days);
        }

        return $routine->load('days');
    }

    /**
     * @throws Exception
     */
    public function update(int $id, array $data): object
    {
        $days = $data['days'] ?? [];
        unset($data['days']);

        $item = ClassRoutine::findOrFail($id);
        $data = $this->prepareForDB($data, $item);

        parent::update($id, $data);

        // Sync days after update
        if (! empty($days)) {
            $item->days()->sync($days);
        }

        return $this->getById($id)->load('days');
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
            $data['status'] = 1;
        } else {

            $data['updated_at'] = now();
        }

        return $data;
    }

    protected function getExceptionMessages(): array
    {
        return [
            static::MESSAGE_ITEM_DOES_NOT_EXIST_MESSAGE => 'ClassRoutine does not exist.',
            static::MESSAGE_ITEM_COULD_NOT_BE_DELETED => 'ClassRoutine could not be deleted.',
        ];
    }
}
