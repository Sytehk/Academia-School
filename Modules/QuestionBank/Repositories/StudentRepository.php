<?php

namespace Modules\QuestionBank\Repositories;

use App\Abstracts\EntityRepository;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Authentication\Models\User;
use Symfony\Component\HttpFoundation\Response;

class StudentRepository extends EntityRepository
{
    public string $table = User::TABLE_NAME;

    protected array $fillableColumns = [
        'institute_id',
        'name',
        'username',
        'email',
        'phone',
        'password',
        'email_verified_at',
        'role_id',
        'user_type',
        'address',
        'avatar',
        'platform',
        'last_active_time',
        'status',
        'created_by',
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

    private function getUserQuery(): EloquentBuilder
    {
        return User::with('deviceControl', 'devices')
            ->where('user_type', 'Student')
            ->select(
                'users.id',
                'users.institute_id',
                'users.name',
                'users.username',
                'users.email',
                'users.phone',
                'users.address',
                'users.avatar',
                'users.status',
                'users.role_id',
                'users.user_type',
                'users.last_active_time',
                'users.status',
                'users.created_at',
                'users.updated_at',
                'users.deleted_at',
            );
    }

    protected function filterSearchQuery(Builder|EloquentBuilder $query, string $searchedText): EloquentBuilder
    {
        $searchable = "%$searchedText%";

        return $query->where(function ($q) use ($searchable) {
            $q->where('students.name', 'LIKE', $searchable)
                ->orWhere('students.email', 'LIKE', $searchable)
                ->orWhere('students.phone', 'LIKE', $searchable)
                ->orWhere('students.status', 'LIKE', $searchable);
        });
    }

    /**
     * @throws Exception
     */
    public function getByColumn(string $columnName, $columnValue, array $selects = ['*']): ?object
    {
        $student = $this->getUserQuery()
            ->where($columnName, $columnValue)
            ->first();

        if (empty($student)) {
            throw new Exception(
                $this->getExceptionMessage(static::MESSAGE_ITEM_DOES_NOT_EXIST_MESSAGE),
                Response::HTTP_NOT_FOUND
            );
        }

        return $student;
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
            // Begin DB transaction
            DB::beginTransaction();
            $data = $this->prepareForDB($data);
            $userData = [
                'institute_id' => $this->getCurrentInstituteId(),
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'avatar' => $data['avatar'] ?? null,
                'role_id' => 3,
                'password' => Hash::make('123456'),
                'user_type' => 'Student',
                'created_at' => now(),
            ];
            $user = User::create($userData);

            // Commit transaction
            DB::commit();

            return $user;
        } catch (Exception $exception) {
            DB::rollBack();

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
            $data['institute_id'] = $this->getCurrentInstituteId();
            $data['status'] = 1;
            if (! empty($data['avatar']) && $data['avatar'] instanceof UploadedFile) {
                $data['avatar'] = fileUploader('users/', 'png', $data['avatar']);
            }
        } else {
            $data['updated_at'] = now();

            if (! empty($data['avatar']) && $data['avatar'] instanceof UploadedFile) {
                $data['avatar'] = fileUploader('users/', 'png', $data['avatar'], $item->avatar);
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
            $student = $this->getById($id);
            $data = $this->prepareForDB($data, $student);
            parent::update($id, $data);

            return $this->getById($id);
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    protected function getExceptionMessages(): array
    {
        $exceptionMessages = parent::getExceptionMessages();

        $studentExceptionMessages = [
            static::MESSAGE_ITEM_DOES_NOT_EXIST_MESSAGE => 'User does not exist.',
            static::MESSAGE_ITEM_COULD_NOT_BE_DELETED => 'User could not be deleted.',
        ];

        return array_merge($exceptionMessages, $studentExceptionMessages);
    }
}
