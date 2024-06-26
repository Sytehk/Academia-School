<?php

namespace Modules\Academic\Repositories;

use App\Abstracts\EntityRepository;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Modules\Academic\Models\CustomField;
use Symfony\Component\HttpFoundation\Response;

class CustomFieldRepository extends EntityRepository
{
    public string $table = CustomField::TABLE_NAME;

    protected array $fillableColumns = [
        'institute_id',
        'branch_id',
        'module',
        'field_name',
        'label',
        'field_type',
        'options',
        'is_required',
        'show_in_form',
        'show_in_list',
        'show_in_profile',
        'serial',
        'status',
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

    private function getCustomFieldQuery(): Builder
    {
        return $this->getQuery()
            ->select(
                "{$this->table}.id",
                "{$this->table}.institute_id",
                "{$this->table}.branch_id",
                "{$this->table}.module",
                "{$this->table}.field_name",
                "{$this->table}.label",
                "{$this->table}.field_type",
                "{$this->table}.options",
                "{$this->table}.is_required",
                "{$this->table}.show_in_form",
                "{$this->table}.show_in_list",
                "{$this->table}.show_in_profile",
                "{$this->table}.serial",
                "{$this->table}.status",
                "{$this->table}.created_at",
                "{$this->table}.updated_at",
                "{$this->table}.deleted_at"
            );
    }

    protected function filterSearchQuery(Builder|EloquentBuilder $query, string $searchedText): Builder
    {
        $searchable = "%$searchedText%";

        return $query->where("{$this->table}.field_name", 'LIKE', $searchable)
            ->orWhere("{$this->table}.status", 'LIKE', $searchable);
    }

    public function getAll(array $filterData = []): Paginator
    {
        $filter = $this->getFilterData($filterData);
        $query = $this->getCustomFieldQuery();

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
        $item = $this->getCustomFieldQuery()
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

        return CustomField::find($id);
    }

    /**
     * @throws Exception
     */
    public function update(int $id, array $data): object
    {
        $item = CustomField::findOrFail($id);
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

        // Convert options array to JSON
        if (isset($data['options']) && is_array($data['options'])) {
            $data['options'] = json_encode($data['options']);
        }

        // Normalize booleans
        $data['is_required'] = $data['is_required'] ?? 0;
        $data['show_in_form'] = $data['show_in_form'] ?? 1;
        $data['show_in_list'] = $data['show_in_list'] ?? 0;
        $data['show_in_profile'] = $data['show_in_profile'] ?? 1;

        if (empty($item)) {
            $data['created_at'] = now();
            $data['institute_id'] = $this->getCurrentInstituteId();
            $data['branch_id'] = $this->getCurrentBranchId();
            $data['status'] = $data['status'] ?? 1;
        } else {
            // institute_id/branch_id are in $fillableColumns for create-time
            // scoping, but must never be reassignable on update — a client
            // could otherwise move a record to a different tenant.
            unset($data['institute_id'], $data['branch_id']);
            $data['updated_at'] = now();
        }

        return $data;
    }

    protected function getExceptionMessages(): array
    {
        return [
            static::MESSAGE_ITEM_DOES_NOT_EXIST_MESSAGE => 'CustomField does not exist.',
            static::MESSAGE_ITEM_COULD_NOT_BE_DELETED => 'CustomField could not be deleted.',
        ];
    }
}
