<?php

namespace Modules\Academic\Repositories;

use App\Abstracts\EntityRepository;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Modules\Academic\Models\OptionalSubjectConfig;
use Modules\Academic\Models\OptionalSubjectItem;
use Modules\Academic\Models\StudentOptionalSubject;
use Symfony\Component\HttpFoundation\Response;

class OptionalSubjectConfigRepository extends EntityRepository
{
    public string $table = OptionalSubjectConfig::TABLE_NAME;

    protected array $fillableColumns = [
        'institute_id',
        'branch_id',
        'class_id',
        'group_id',
        'name',
        'total_mark',
        'selection_limit',
        'is_active',
    ];

    protected function filterSearchQuery(Builder|\Illuminate\Database\Eloquent\Builder $query, string $searchedText): Builder|\Illuminate\Database\Eloquent\Builder
    {
        return $query->where("{$this->table}.name", 'LIKE', "%{$searchedText}%");
    }

    public function getAll(array $filterData = []): Paginator
    {
        $filter = $this->getFilterData($filterData);
        // getQuery() returns a plain query-builder instance, so Eloquent's
        // SoftDeletes global scope does not apply here — filter explicitly.
        $query = $this->getQuery()->whereNull("{$this->table}.deleted_at");

        if (! empty($filter['search'])) {
            $query = $this->filterSearchQuery($query, $filter['search']);
        }

        return $query->orderBy($filter['orderBy'], $filter['order'])->paginate($filter['perPage']);
    }

    /**
     * @throws Exception
     */
    public function getById(int $id, array $selects = ['*']): ?object
    {
        $config = OptionalSubjectConfig::with('items.subject', 'class', 'group')
            ->where('institute_id', $this->getCurrentInstituteId())
            ->where('branch_id', $this->getCurrentBranchId())
            ->find($id);

        if (! $config) {
            throw new Exception(
                $this->getExceptionMessage(static::MESSAGE_ITEM_DOES_NOT_EXIST_MESSAGE),
                Response::HTTP_NOT_FOUND
            );
        }

        return $config;
    }

    /**
     * @throws Exception
     */
    public function create(array $data): object
    {
        $this->assertSubjectsProvided($data);
        $this->assertNoDuplicateName($data);

        return DB::transaction(function () use ($data) {
            $config = OptionalSubjectConfig::create([
                'institute_id' => $this->getCurrentInstituteId(),
                'branch_id' => $this->getCurrentBranchId(),
                'class_id' => $data['class_id'],
                'group_id' => $data['group_id'] ?? null,
                'name' => $data['name'],
                'total_mark' => $data['total_mark'],
                'selection_limit' => $data['selection_limit'],
                'is_active' => $data['is_active'] ?? true,
            ]);

            $this->syncItems($config, $data['subjects']);

            return $this->getById($config->id);
        });
    }

    /**
     * @throws Exception
     */
    public function update(int $id, array $data): ?object
    {
        $config = $this->requireConfig($id);
        $this->assertSubjectsProvided($data);
        $this->assertNoDuplicateName($data, $id);

        return DB::transaction(function () use ($config, $data) {
            $config->update([
                'class_id' => $data['class_id'] ?? $config->class_id,
                'group_id' => $data['group_id'] ?? $config->group_id,
                'name' => $data['name'] ?? $config->name,
                'total_mark' => $data['total_mark'] ?? $config->total_mark,
                'selection_limit' => $data['selection_limit'] ?? $config->selection_limit,
                'is_active' => $data['is_active'] ?? $config->is_active,
            ]);

            $config->items()->delete();
            $this->syncItems($config, $data['subjects']);

            return $this->getById($config->id);
        });
    }

    /**
     * @throws Exception
     */
    public function delete(int $id): ?object
    {
        $config = $this->requireConfig($id);

        if (StudentOptionalSubject::where('optional_subject_config_id', $id)->exists()) {
            throw new Exception(
                'This optional subject config could not be deleted because students already have picks recorded against it.',
                Response::HTTP_CONFLICT
            );
        }

        $config->delete();

        return $config;
    }

    private function syncItems(OptionalSubjectConfig $config, array $subjects): void
    {
        foreach ($subjects as $key => $item) {
            $subjectId = is_array($item) ? ($item['subject_id'] ?? null) : $item;

            if (! $subjectId) {
                continue;
            }

            OptionalSubjectItem::create([
                'institute_id' => $this->getCurrentInstituteId(),
                'branch_id' => $this->getCurrentBranchId(),
                'optional_subject_config_id' => $config->id,
                'subject_id' => $subjectId,
                'mark' => is_array($item) ? ($item['mark'] ?? 100) : 100,
                'priority' => is_array($item) ? ($item['priority'] ?? $key) : $key,
            ]);
        }
    }

    /**
     * @throws Exception
     */
    private function assertSubjectsProvided(array $data): void
    {
        if (empty($data['subjects']) || ! is_array($data['subjects'])) {
            throw new Exception('At least one subject must be selected.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $subjectIds = array_map(
            fn ($item) => is_array($item) ? ($item['subject_id'] ?? null) : $item,
            $data['subjects']
        );

        if (count($subjectIds) !== count(array_unique($subjectIds))) {
            throw new Exception('The same subject was selected more than once.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * @throws Exception
     */
    private function assertNoDuplicateName(array $data, ?int $ignoreId = null): void
    {
        if (empty($data['name']) || empty($data['class_id'])) {
            return;
        }

        $exists = OptionalSubjectConfig::where('institute_id', $this->getCurrentInstituteId())
            ->where('class_id', $data['class_id'])
            ->where('group_id', $data['group_id'] ?? null)
            ->where('name', $data['name'])
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists();

        if ($exists) {
            throw new Exception('An optional subject config with this name already exists for this class/group.', Response::HTTP_CONFLICT);
        }
    }

    /**
     * @throws Exception
     */
    private function requireConfig(int $id): OptionalSubjectConfig
    {
        $config = OptionalSubjectConfig::where('institute_id', $this->getCurrentInstituteId())
            ->where('branch_id', $this->getCurrentBranchId())
            ->find($id);

        if (! $config) {
            throw new Exception(
                $this->getExceptionMessage(static::MESSAGE_ITEM_DOES_NOT_EXIST_MESSAGE),
                Response::HTTP_NOT_FOUND
            );
        }

        return $config;
    }

    protected function getExceptionMessages(): array
    {
        return [
            static::MESSAGE_ITEM_DOES_NOT_EXIST_MESSAGE => 'Optional subject config does not exist.',
            static::MESSAGE_ITEM_COULD_NOT_BE_DELETED => 'Optional subject config could not be deleted.',
        ];
    }
}
