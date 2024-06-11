<?php

namespace App\Abstracts;

use App\Database\TenantScopedQueryBuilder;
use App\Entity\Dropdown;
use App\Interfaces\CrudInterface;
use App\Interfaces\DBPrepareInterface;
use App\Support\TenantTables;
use App\Traits\Authenticatable;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

abstract class EntityRepository implements CrudInterface, DBPrepareInterface
{
    use Authenticatable;

    protected const MESSAGE_ITEM_DOES_NOT_EXIST_MESSAGE = 'item_does_not_exist';

    protected const MESSAGE_ITEM_COULD_NOT_BE_DELETED = 'item_could_not_be_deleted';

    protected string $table;

    protected string $primaryKey = 'id';

    protected array $messages = [];

    protected array $fillableColumns = [];

    /**
     * @throws Exception
     */
    public function getAll(array $filterData = []): Paginator
    {
        try {
            $filter = $this->getFilterData($filterData);
            $query = $this->getQuery();

            if (isset($filter['search']) && strlen($filter['search']) > 0) {
                $query = $this->filterSearchQuery($query, $filter['search']);
            }

            return $query->orderBy($filter['orderBy'], $filter['order'])
                ->paginate($filter['perPage']);
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    protected function getFilterData(array $filterData = []): array
    {
        $defaultArgs = [
            'perPage' => 10,
            'search' => '',
            'orderBy' => "{$this->table}.{$this->primaryKey}",
            'order' => 'desc',
        ];

        return array_merge($defaultArgs, $filterData);
    }

    protected function getQuery(): Builder
    {
        // Tables that should not be filtered by institute_id or branch_id
        $globalTables = [
            'institutes',
            'plans',
            's_a_a_s_faqs',
            's_a_a_s_subscriptions',
            'academic_images',
            'institute_image_s_a_a_s_settings',
            'institute_image_settings',

            's_a_a_s_banners',
            's_a_a_s_cores',
            's_a_a_s_panels',
            's_a_a_s_benefits',
            's_a_a_s_solutions',
            's_a_a_s_feedback',
            's_a_a_s_policies',
            'institute_types',
            'currencies',

            // Course / LMS related tables
            'course_categories',
            'course_features',
            'course_faqs',
            'chapters',
            'lessons',
            'lms_assignments',
            'lms_assignment_results',
            'contents',
            'lms_zoom_meetings',
            'lms_rooms',
            'lms_days',
            'lms_class_routines',
            'lms_class_routine_days',
            'content_visibility',
            'enrollments',
        ];

        // If current table is global, return direct query
        if (in_array($this->table, $globalTables, true)) {
            return DB::table($this->table);
        }

        // Question bank: institute-owned, with a read-only shared library for
        // the SHARED taxonomy tables. Writes are guarded by the builder itself.
        if (TenantTables::isShared($this->table) || TenantTables::isInstituteOnly($this->table)) {
            $connection = DB::connection();
            $query = (new TenantScopedQueryBuilder(
                $connection,
                $connection->getQueryGrammar(),
                $connection->getPostProcessor()
            ))->from($this->table);
            TenantTables::applyReadScope($query, $this->table);

            return $query;
        }

        // Otherwise, apply institute & branch filtering
        $instituteId = (int) $this->getCurrentInstituteId();
        $branchId = (int) $this->getCurrentBranchId();

        return DB::table($this->table)
            ->where("{$this->table}.institute_id", $instituteId)
            ->where("{$this->table}.branch_id", $branchId);
    }

    protected function filterSearchQuery(Builder|EloquentBuilder $query, string $searchedText): Builder|EloquentBuilder
    {
        return $query;
    }

    public function getCount(array $filterData = []): int
    {
        return $this->getQuery()->count();
    }

    /**
     * @throws Exception
     */
    public function create(array $data): bool|object
    {
        $data = $this->prepareForDB($data);

        try {
            return $this->getQuery()->insert($data);
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @throws Exception
     */
    public function update(int $id, array $data): ?object
    {
        $item = $this->getById($id);

        try {
            $data = $this->prepareForDB($data, $item);
            $updated = $this->getQuery()
                ->where("{$this->table}.{$this->primaryKey}", $id)
                ->update($data);

            if ($updated) {
                $item = $this->getById($id);
            }

            return $item;
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @throws Exception
     */
    public function getById(int $id, array $selects = ['*']): ?object
    {
        return $this->getByColumn("{$this->table}.{$this->primaryKey}", $id, $selects);
    }

    public function prepareForDB(array $data, ?object $item = null): array
    {
        if (count($this->fillableColumns) === 0) {
            return $data;
        }

        $preparedData = [];
        foreach ($this->fillableColumns as $column) {
            if (isset($data[$column])) {
                $preparedData[$column] = $data[$column] ?? null;
            }
        }

        return $preparedData;
    }

    /**
     * @throws Exception
     */
    public function getByColumn(string $columnName, $columnValue, array $selects = ['*']): ?object
    {
        try {
            $item = $this->getQuery()
                ->where($columnName, $columnValue)
                ->select($selects)
                ->first();

            if (empty($item)) {
                throw new Exception(
                    $this->getExceptionMessage(static::MESSAGE_ITEM_DOES_NOT_EXIST_MESSAGE),
                    Response::HTTP_NOT_FOUND
                );
            }

            return $item;
        } catch (Exception $exception) {
            throw new Exception($exception);
        }
    }

    protected function getExceptionMessage(string $key): string
    {
        return $this->getExceptionMessages()[$key];
    }

    protected function getExceptionMessages(): array
    {
        return [
            static::MESSAGE_ITEM_DOES_NOT_EXIST_MESSAGE => 'Item does not exist.',
            static::MESSAGE_ITEM_COULD_NOT_BE_DELETED => 'Item could not be deleted.',
        ];
    }

    /**
     * @throws Exception
     */
    public function delete(int $id): ?object
    {
        $item = $this->getById($id);

        try {
            $deleted = $this->getQuery()
                ->where($this->primaryKey, $id)
                ->update([
                    'deleted_at' => Carbon::now(),
                ]);
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        if (! $deleted) {
            throw new Exception(
                $this->getExceptionMessage(static::MESSAGE_ITEM_COULD_NOT_BE_DELETED),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return $item;
    }

    /**
     * @throws Exception
     */
    public function getDropdown(): array
    {
        $columns = array_unique(
            array_merge(
                [$this->primaryKey],
                $this->getDropdownSelectableColumns()
            )
        );

        $dropdown = (new Dropdown)
            ->setTableName($this->table)
            ->setSelectedColumns($columns)
            ->setOrderByColumnWithOrders($this->getOrderByColumnWithOrders())
            ->setSelectableTableQuery();

        $dropdown->setQuery($this->getDropdownAdditionalWhere($dropdown->getQuery()));

        return $dropdown->getDropdowns();
    }

    /**
     * @throws Exception
     */
    protected function getDropdownSelectableColumns(): array
    {
        throw new Exception('This method must be called from child repository.');
    }

    /**
     * @throws Exception
     */
    protected function getOrderByColumnWithOrders(): array
    {
        throw new Exception('This method must be called from child repository.');
    }

    protected function getDropdownAdditionalWhere(Builder $query): Builder
    {
        return $query;
    }

    protected function getNextAutoIncrementId(): int
    {
        $tableInfo = DB::select("SHOW CREATE TABLE {$this->table}");
        $tableCreateStatement = $tableInfo[0]->{'Create Table'};
        preg_match('/AUTO_INCREMENT=(\d+)/', $tableCreateStatement, $matches);

        return $matches[1] ?? 1;
    }
}
