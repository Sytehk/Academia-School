<?php

namespace Modules\Academic\Repositories;

use App\Abstracts\EntityRepository;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\UploadedFile;
use Modules\Academic\Models\TeacherSignature;
use Symfony\Component\HttpFoundation\Response;

class TeacherSignatureRepository extends EntityRepository
{
    public string $table = TeacherSignature::TABLE_NAME;

    protected array $fillableColumns = [
        'institute_id',
        'teacher_id',
        'signature_image',
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

    private function getTeacherSignatureQuery(): Builder
    {
        return $this->getQuery()
            ->leftJoin('teachers', 'teachers.id', '=', "{$this->table}.teacher_id")
            ->select(
                "{$this->table}.id",
                "{$this->table}.institute_id",
                "{$this->table}.teacher_id",
                'teachers.name as teacher_name',
                "{$this->table}.signature_image",
                "{$this->table}.status",
                "{$this->table}.created_at",
                "{$this->table}.deleted_at"
            );
    }

    protected function filterSearchQuery(Builder|EloquentBuilder $query, string $searchedText): Builder
    {
        $searchable = "%$searchedText%";

        return $query->where("{$this->table}.teacher_id", 'LIKE', $searchable)
            ->orWhere("{$this->table}.status", 'LIKE', $searchable);
    }

    public function getAll(array $filterData = []): Paginator
    {
        $filter = $this->getFilterData($filterData);
        $query = $this->getTeacherSignatureQuery();

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
        $item = $this->getTeacherSignatureQuery()
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

        return TeacherSignature::find($id);
    }

    /**
     * @throws Exception
     */
    public function update(int $id, array $data): object
    {
        $item = TeacherSignature::findOrFail($id);
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
            $data['teacher_id'] = get_teacher_id();

            if (! empty($data['signature_image']) && $data['signature_image'] instanceof UploadedFile) {
                $data['signature_image'] = fileUploader('teacher_signature_images/', 'png', $data['signature_image']);
            }
        } else {
            if (! empty($data['signature_image']) && $data['signature_image'] instanceof UploadedFile) {
                $data['signature_image'] = fileUploader('teacher_signature_images/', 'png', $data['signature_image'], $item->signature_image);
            }

            // institute_id/branch_id/teacher_id must never be reassignable on
            // update — a client could otherwise move this record to a
            // different tenant or teacher.
            unset($data['institute_id'], $data['branch_id'], $data['teacher_id']);
            $data['updated_at'] = now();
        }

        return $data;
    }

    protected function getExceptionMessages(): array
    {
        return [
            static::MESSAGE_ITEM_DOES_NOT_EXIST_MESSAGE => 'TeacherSignature does not exist.',
            static::MESSAGE_ITEM_COULD_NOT_BE_DELETED => 'TeacherSignature could not be deleted.',
        ];
    }
}
