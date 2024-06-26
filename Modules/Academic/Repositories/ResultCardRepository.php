<?php

namespace Modules\Academic\Repositories;

use App\Abstracts\EntityRepository;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\UploadedFile;
use Modules\Academic\Models\ResultCard;
use Symfony\Component\HttpFoundation\Response;

class ResultCardRepository extends EntityRepository
{
    public string $table = ResultCard::TABLE_NAME;

    protected array $fillableColumns = [
        'institute_id',
        'name',
        'qr_code',
        'signature_id',
        'teacher_signature_id',
        'background_image',
        'header_logo',
        'stamp_image',
        'border_design',
        'watermark',
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

    private function getResultCardQuery(): Builder
    {
        return $this->getQuery()
            // Join for regular signature
            ->leftJoin('signatures as s1', 's1.id', '=', "{$this->table}.signature_id")
            // Join for teacher signature
            ->leftJoin('signatures as s2', 's2.id', '=', "{$this->table}.teacher_signature_id")
            ->select(
                "{$this->table}.id",
                "{$this->table}.institute_id",
                "{$this->table}.name",
                "{$this->table}.qr_code",

                "{$this->table}.signature_id",
                's1.image as signature_image',

                "{$this->table}.teacher_signature_id",
                's2.image as teacher_signature_image',

                "{$this->table}.background_image",
                "{$this->table}.header_logo",
                "{$this->table}.stamp_image",
                "{$this->table}.border_design",
                "{$this->table}.watermark",

                "{$this->table}.status",
                "{$this->table}.created_at",
                "{$this->table}.deleted_at"
            );
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
        $query = $this->getResultCardQuery();

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
        $item = $this->getResultCardQuery()
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

        return ResultCard::find($id);
    }

    /**
     * @throws Exception
     */
    public function update(int $id, array $data): object
    {
        $item = ResultCard::findOrFail($id);
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

            if (! empty($data['background_image']) && $data['background_image'] instanceof UploadedFile) {
                $data['background_image'] = fileUploader('result_card_images/', 'png', $data['background_image']);
            }

            if (! empty($data['header_logo']) && $data['header_logo'] instanceof UploadedFile) {
                $data['header_logo'] = fileUploader('result_card_images/', 'png', $data['header_logo']);
            }

            if (! empty($data['stamp_image']) && $data['stamp_image'] instanceof UploadedFile) {
                $data['stamp_image'] = fileUploader('result_card_images/', 'png', $data['stamp_image']);
            }

            if (! empty($data['border_design']) && $data['border_design'] instanceof UploadedFile) {
                $data['border_design'] = fileUploader('result_card_images/', 'png', $data['border_design']);
            }

            if (! empty($data['watermark']) && $data['watermark'] instanceof UploadedFile) {
                $data['watermark'] = fileUploader('result_card_images/', 'png', $data['watermark']);
            }
        } else {
            if (! empty($data['background_image']) && $data['background_image'] instanceof UploadedFile) {
                $data['background_image'] = fileUploader('result_card_images/', 'png', $data['background_image'], $item->background_image);
            }

            if (! empty($data['header_logo']) && $data['header_logo'] instanceof UploadedFile) {
                $data['header_logo'] = fileUploader('result_card_images/', 'png', $data['header_logo'], $item->header_logo);
            }

            if (! empty($data['stamp_image']) && $data['stamp_image'] instanceof UploadedFile) {
                $data['stamp_image'] = fileUploader('result_card_images/', 'png', $data['stamp_image'], $item->stamp_image);
            }

            if (! empty($data['border_design']) && $data['border_design'] instanceof UploadedFile) {
                $data['border_design'] = fileUploader('result_card_images/', 'png', $data['border_design'], $item->border_design);
            }

            if (! empty($data['watermark']) && $data['watermark'] instanceof UploadedFile) {
                $data['watermark'] = fileUploader('result_card_images/', 'png', $data['watermark'], $item->watermark);
            }

            // institute_id/branch_id must never be reassignable on update — a
            // client could otherwise move this record to a different tenant.
            unset($data['institute_id'], $data['branch_id']);
            $data['updated_at'] = now();
        }

        return $data;
    }

    protected function getExceptionMessages(): array
    {
        return [
            static::MESSAGE_ITEM_DOES_NOT_EXIST_MESSAGE => 'ResultCard does not exist.',
            static::MESSAGE_ITEM_COULD_NOT_BE_DELETED => 'ResultCard could not be deleted.',
        ];
    }
}
