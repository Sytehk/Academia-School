<?php

namespace Modules\Authentication\Repositories;

use App\Abstracts\EntityRepository;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Modules\Authentication\Models\User;
use Symfony\Component\HttpFoundation\Response;

class UserRepository extends EntityRepository
{
    public string $table = User::TABLE_NAME;

    protected array $fillableColumns = [
        'name',
        'username',
        'email',
        'phone',
        'password',
        'otp_code',
        'isVerified',
        'email_verified_at',
        'address',
        'image',
        'status',
        'role_id',
        'institute_id',
        'user_type',
        'facebook',
        'twitter',
        'linkedin',
        'google_plus',
        'nid',
        'platform',
        'device_info',
        'last_active_time',
        'created_by',
        'nid_number',
        'evidence',
        'proof_docs',
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
        $query = $this->getUserQuery();

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

    private function getUserQuery(): Builder
    {
        return $this->getQuery()->select(
            'users.id',
            'users.institute_id',
            'users.first_name',
            'users.last_name',
            'users.username',
            'users.email',
            'users.phone',
            'users.address',
            'users.avatar',
            'users.status',
            'users.role_id',
            'users.user_type',
            'users.last_active_time',
            'users.created_by'
        );
    }

    protected function filterSearchQuery(Builder|EloquentBuilder $query, string $searchedText): Builder
    {
        $searchable = "%$searchedText%";

        return $query->where('users.first_name', 'LIKE', $searchable)
            ->orWhere('users.last_name', 'LIKE', $searchable)
            ->orWhere('users.email', 'LIKE', $searchable)
            ->orWhere('users.phone', 'LIKE', $searchable)
            ->orWhere('users.status', 'LIKE', $searchable);
    }

    /**
     * @throws Exception
     */
    public function getByColumn(string $columnName, $columnValue, array $selects = ['*']): ?object
    {
        $user = $this->getUserQuery()
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
            $data['password'] = empty($data['password']) ? '123456' : $data['password'];
            $data = $this->prepareForDB($data);
            $userId = $this->getQuery()->insertGetId($data);
            $user = User::find($userId);

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
            if (! empty($data['avatar']) && $data['avatar'] instanceof UploadedFile) {
                $data['avatar'] = fileUploader('users/', 'png', $data['avatar']);
            }
        } else {
            if (! empty($data['avatar']) && $data['avatar'] instanceof UploadedFile) {
                $data['avatar'] = fileUploader('users/', 'png', $data['avatar'], $item->avatar);
            }
            $data['updated_at'] = now();
        }

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        return $data;
    }

    /**
     * @throws Exception
     */
    public function update(int $id, array $data): ?object
    {
        try {
            $user = User::find($id);
            $data = $this->prepareForDB($data, $user);
            parent::update($id, $data);

            return $this->getById($user->id);
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @throws Exception
     */
    public function getDropdown(): array
    {
        $dropdowns = parent::getDropdown();
        $dropdownsData = [];
        foreach ($dropdowns as $dropdownItem) {
            $dropdownItem->name = $dropdownItem->first_name.' '.$dropdownItem->last_name.' #'.$dropdownItem->id;
            unset($dropdownItem->first_name, $dropdownItem->last_name, $dropdownItem->phone);
            $dropdownsData[] = $dropdownItem;
        }

        return $dropdownsData;
    }

    protected function getExceptionMessages(): array
    {
        $exceptionMessages = parent::getExceptionMessages();

        $userExceptionMessages = [
            static::MESSAGE_ITEM_DOES_NOT_EXIST_MESSAGE => 'User does not exist.',
            static::MESSAGE_ITEM_COULD_NOT_BE_DELETED => 'User could not be deleted.',
        ];

        return array_merge($exceptionMessages, $userExceptionMessages);
    }

    protected function getDropdownSelectableColumns(): array
    {
        return [
            'first_name',
            'last_name',
        ];
    }

    protected function getOrderByColumnWithOrders(): array
    {
        return [
            'first_name' => 'asc',
        ];
    }
}
