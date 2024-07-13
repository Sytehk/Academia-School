<?php

namespace Modules\Frontend\Repositories;

use App\Abstracts\EntityRepository;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\UploadedFile;
use Modules\Frontend\Models\AdmissionForm;
use Symfony\Component\HttpFoundation\Response;

class AdmissionFormRepository extends EntityRepository
{
    public string $table = AdmissionForm::TABLE_NAME;

    protected array $fillableColumns = [
        'institute_id',
        'branch_id',
        'class_id',
        'section_id',
        'group_id',
        'first_name',
        'last_name',
        'father_name',
        'mother_name',
        'email',
        'phone',
        'birthday',
        'gender',
        'blood_group',
        'religion',
        'address',
        'registration_no',
        'previous_school',
        'previous_class',
        'guardian_name',
        'guardian_email',
        'guardian_phone',
        'guardian_relation',
        'guardian_address',
        'image',
        'remarks',
        'status',
        'approved_at',
        'approved_by',
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

    private function getAdmissionFormQuery(): Builder
    {
        return $this->getQuery()
            ->select(
                "{$this->table}.id",
                "{$this->table}.institute_id",
                "{$this->table}.branch_id",
                "{$this->table}.class_id",
                "{$this->table}.section_id",
                "{$this->table}.group_id",
                "{$this->table}.first_name",
                "{$this->table}.last_name",
                "{$this->table}.father_name",
                "{$this->table}.mother_name",
                "{$this->table}.email",
                "{$this->table}.phone",
                "{$this->table}.birthday",
                "{$this->table}.gender",
                "{$this->table}.blood_group",
                "{$this->table}.religion",
                "{$this->table}.address",
                "{$this->table}.registration_no",
                "{$this->table}.previous_school",
                "{$this->table}.previous_class",
                "{$this->table}.guardian_name",
                "{$this->table}.guardian_email",
                "{$this->table}.guardian_phone",
                "{$this->table}.guardian_relation",
                "{$this->table}.guardian_address",
                "{$this->table}.image",
                "{$this->table}.remarks",
                "{$this->table}.status",
                "{$this->table}.approved_at",
                "{$this->table}.approved_by",
                "{$this->table}.created_at",
                "{$this->table}.updated_at",
                "{$this->table}.deleted_at"
            );
    }

    protected function filterSearchQuery(Builder|EloquentBuilder $query, string $searchedText): Builder
    {
        $searchable = "%$searchedText%";

        return $query->where("{$this->table}.first_name", 'LIKE', $searchable)
            ->orWhere("{$this->table}.class_id", 'LIKE', $searchable);
    }

    public function getAll(array $filterData = []): Paginator
    {
        $filter = $this->getFilterData($filterData);
        $query = $this->getAdmissionFormQuery();

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
        $item = $this->getAdmissionFormQuery()
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

        return AdmissionForm::find($id);
    }

    /**
     * @throws Exception
     */
    public function update(int $id, array $data): object
    {
        $item = AdmissionForm::findOrFail($id);
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
                $data['image'] = fileUploader('users/', 'png', $data['image']);
            }
        } else {
            if (! empty($data['image']) && $data['image'] instanceof UploadedFile) {
                $data['image'] = fileUploader('users/', 'png', $data['image'], $item->image);
            }

            $data['updated_at'] = now();
        }

        return $data;
    }

    protected function getExceptionMessages(): array
    {
        return [
            static::MESSAGE_ITEM_DOES_NOT_EXIST_MESSAGE => 'AdmissionForm does not exist.',
            static::MESSAGE_ITEM_COULD_NOT_BE_DELETED => 'AdmissionForm could not be deleted.',
        ];
    }
}
