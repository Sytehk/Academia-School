<?php

namespace Modules\Authentication\Repositories;

use App\Abstracts\EntityRepository;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\UploadedFile;
use Modules\Authentication\Models\InstituteImageSetting;
use Symfony\Component\HttpFoundation\Response;

class InstituteImageSettingRepository extends EntityRepository
{
    public string $table = InstituteImageSetting::TABLE_NAME;

    protected array $fillableColumns = [
        'institute_id',
        'header_logo_light_theme',
        'header_logo_dark_theme',
        'footer_logo_light_theme',
        'footer_logo_dark_theme',
        'favicon',
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

    private function getInstituteImageSettingQuery(): Builder
    {
        return $this->getQuery()
            ->select(
                "{$this->table}.id",
                "{$this->table}.institute_id",
                "{$this->table}.header_logo_light_theme",
                "{$this->table}.header_logo_dark_theme",
                "{$this->table}.footer_logo_light_theme",
                "{$this->table}.footer_logo_dark_theme",
                "{$this->table}.favicon",
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
        $query = $this->getInstituteImageSettingQuery();

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
        $item = $this->getInstituteImageSettingQuery()
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

        return InstituteImageSetting::find($id);
    }

    /**
     * @throws Exception
     */
    public function update(int $id, array $data): object
    {
        $item = InstituteImageSetting::findOrFail($id);
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
            $data['status'] = 1;

            if (! empty($data['header_logo_light_theme']) && $data['header_logo_light_theme'] instanceof UploadedFile) {
                $data['header_logo_light_theme'] = fileUploader('institute_image_settings/', 'png', $data['header_logo_light_theme']);
            }

            if (! empty($data['header_logo_dark_theme']) && $data['header_logo_dark_theme'] instanceof UploadedFile) {
                $data['header_logo_dark_theme'] = fileUploader('institute_image_settings/', 'png', $data['header_logo_dark_theme']);
            }

            if (! empty($data['footer_logo_light_theme']) && $data['footer_logo_light_theme'] instanceof UploadedFile) {
                $data['footer_logo_light_theme'] = fileUploader('institute_image_settings/', 'png', $data['footer_logo_light_theme']);
            }

            if (! empty($data['footer_logo_dark_theme']) && $data['footer_logo_dark_theme'] instanceof UploadedFile) {
                $data['footer_logo_dark_theme'] = fileUploader('institute_image_settings/', 'png', $data['footer_logo_dark_theme']);
            }

            if (! empty($data['favicon']) && $data['favicon'] instanceof UploadedFile) {
                $data['favicon'] = fileUploader('institute_image_settings/', 'png', $data['favicon']);
            }
        } else {
            if (! empty($data['header_logo_light_theme']) && $data['header_logo_light_theme'] instanceof UploadedFile) {
                $data['header_logo_light_theme'] = fileUploader('institute_image_settings/', 'png', $data['header_logo_light_theme'], $item->header_logo_light_theme);
            }

            if (! empty($data['header_logo_dark_theme']) && $data['header_logo_dark_theme'] instanceof UploadedFile) {
                $data['header_logo_dark_theme'] = fileUploader('institute_image_settings/', 'png', $data['header_logo_dark_theme'], $item->header_logo_dark_theme);
            }

            if (! empty($data['footer_logo_light_theme']) && $data['footer_logo_light_theme'] instanceof UploadedFile) {
                $data['footer_logo_light_theme'] = fileUploader('institute_image_settings/', 'png', $data['footer_logo_light_theme'], $item->footer_logo_light_theme);
            }

            if (! empty($data['footer_logo_dark_theme']) && $data['footer_logo_dark_theme'] instanceof UploadedFile) {
                $data['footer_logo_dark_theme'] = fileUploader('institute_image_settings/', 'png', $data['footer_logo_dark_theme'], $item->footer_logo_dark_theme);
            }

            if (! empty($data['favicon']) && $data['favicon'] instanceof UploadedFile) {
                $data['favicon'] = fileUploader('institute_image_settings/', 'png', $data['favicon'], $item->favicon);
            }

            $data['updated_at'] = now();
        }

        return $data;
    }

    protected function getExceptionMessages(): array
    {
        return [
            static::MESSAGE_ITEM_DOES_NOT_EXIST_MESSAGE => 'InstituteImageSetting does not exist.',
            static::MESSAGE_ITEM_COULD_NOT_BE_DELETED => 'InstituteImageSetting could not be deleted.',
        ];
    }
}
