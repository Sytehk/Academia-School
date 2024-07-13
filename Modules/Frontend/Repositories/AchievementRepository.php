<?php

namespace Modules\Frontend\Repositories;

use App\Abstracts\EntityRepository;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\UploadedFile;
use Modules\Frontend\Models\Achievement;
use Symfony\Component\HttpFoundation\Response;

class AchievementRepository extends EntityRepository
{
    public string $table = Achievement::TABLE_NAME;

    protected array $fillableColumns = [
        'institute_id',
        'branch_id',
        'title',
        'description',
        'image',
        'graduates',
        'certified_teachers',
        'student_campus',
        'students',
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

    private function getAchievementQuery(): Builder
    {
        return $this->getQuery()
            ->select(
                "{$this->table}.id",
                "{$this->table}.institute_id",
                "{$this->table}.institute_id",
                "{$this->table}.branch_id",
                "{$this->table}.title",
                "{$this->table}.description",
                "{$this->table}.image",
                "{$this->table}.graduates",
                "{$this->table}.certified_teachers",
                "{$this->table}.student_campus",
                "{$this->table}.students",
                "{$this->table}.status",
                "{$this->table}.created_at",
                "{$this->table}.updated_at",
                "{$this->table}.deleted_at"
            );
    }

    protected function filterSearchQuery(Builder|EloquentBuilder $query, string $searchedText): Builder
    {
        $searchable = "%$searchedText%";

        return $query->where("{$this->table}.title", 'LIKE', $searchable)
            ->orWhere("{$this->table}.status", 'LIKE', $searchable);
    }

    public function getAll(array $filterData = []): Paginator
    {
        $filter = $this->getFilterData($filterData);
        $query = $this->getAchievementQuery();

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
        $item = $this->getAchievementQuery()
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

        return Achievement::find($id);
    }

    /**
     * @throws Exception
     */
    public function update(int $id, array $data): object
    {
        $item = Achievement::findOrFail($id);
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
            $data['institute_id'] = $this->getCurrentInstituteId();
            $data['branch_id'] = $this->getCurrentBranchId();

            if (! empty($data['image']) && $data['image'] instanceof UploadedFile) {
                $data['image'] = fileUploader('achievements/', 'png', $data['image']);
            }
        } else {
            if (! empty($data['image']) && $data['image'] instanceof UploadedFile) {
                $data['image'] = fileUploader('achievements/', 'png', $data['image'], $item->image);
            }

            $data['updated_at'] = now();
        }

        return $data;
    }

    protected function getExceptionMessages(): array
    {
        return [
            static::MESSAGE_ITEM_DOES_NOT_EXIST_MESSAGE => 'Achievement does not exist.',
            static::MESSAGE_ITEM_COULD_NOT_BE_DELETED => 'Achievement could not be deleted.',
        ];
    }
}
