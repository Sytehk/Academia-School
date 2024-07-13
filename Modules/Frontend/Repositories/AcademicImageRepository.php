<?php

namespace Modules\Frontend\Repositories;

use App\Abstracts\EntityRepository;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\UploadedFile;
use Modules\Frontend\Models\AcademicImage;
use Symfony\Component\HttpFoundation\Response;

class AcademicImageRepository extends EntityRepository
{
    public string $table = AcademicImage::TABLE_NAME;

    protected array $fillableColumns = [
        'institute_id',
        'title',
        'heading',
        'description',
        'image',
        'status',
        'created_by',
        'created_at',
        'updated_at',
    ];

    protected function getQuery(): Builder
    {
        return parent::getQuery();
    }

    public function getAll(array $filterData = []): Paginator
    {
        $filter = $this->getFilterData($filterData);
        $query = $this->getAcademicImageQuery();

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

    private function getAcademicImageQuery(): Builder
    {
        return $this->getQuery()
            ->select(
                'academic_images.id',
                'academic_images.institute_id',
                'academic_images.title',
                'academic_images.heading',
                'academic_images.description',
                'academic_images.image',
                'academic_images.status',
                'academic_images.created_at',
            );
    }

    protected function filterSearchQuery(Builder|EloquentBuilder $query, string $searchedText): Builder
    {
        $searchable = "%$searchedText%";

        return $query->where('academic_images.question', 'LIKE', $searchable)
            ->orWhere('academic_images.title', 'LIKE', $searchable)
            ->orWhere('academic_images.heading', 'LIKE', $searchable)
            ->orWhere('academic_images.status', 'LIKE', $searchable);
    }

    /**
     * @throws Exception
     */
    public function getByColumn(string $columnName, $columnValue, array $selects = ['*']): ?object
    {
        $user = $this->getAcademicImageQuery()
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
            $userId = $this->getQuery()->insertGetId($data);
            $user = AcademicImage::find($userId);

            return $user;
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
            $data['created_at'] = now();
            $data['created_by'] = $this->getCurrentUserId();
            $data['institute_id'] = $this->getCurrentInstituteId();
            $data['status'] = 1;
            if (! empty($data['image']) && $data['image'] instanceof UploadedFile) {
                $data['image'] = fileUploader('academic_images/', 'png', $data['image']);
            }
        } else {
            $data['updated_at'] = now();
            if (! empty($data['image']) && $data['image'] instanceof UploadedFile) {
                $data['image'] = fileUploader('academic_images/', 'png', $data['image'], $item->image);
            }
        }

        return $data;
    }

    /**
     * @throws Exception
     */
    public function update(int $id, array $data): ?object
    {
        try {
            $user = AcademicImage::find($id);
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
            static::MESSAGE_ITEM_DOES_NOT_EXIST_MESSAGE => 'AcademicImage does not exist.',
            static::MESSAGE_ITEM_COULD_NOT_BE_DELETED => 'AcademicImage could not be deleted.',
        ];

        return array_merge($exceptionMessages, $userExceptionMessages);
    }
}
