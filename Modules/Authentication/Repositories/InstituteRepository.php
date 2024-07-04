<?php

namespace Modules\Authentication\Repositories;

use App\Abstracts\EntityRepository;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Modules\Authentication\Models\Institute;
use Symfony\Component\HttpFoundation\Response;

class InstituteRepository extends EntityRepository
{
    public string $table = Institute::TABLE_NAME;

    protected array $fillableColumns = [
        'owner_id',
        'assigned_to',
        'theme_id',
        'name',
        'email',
        'address',
        'institute_type',
        'phone',
        'domain',
        'platform',
        'last_active_time',
        'logo',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected function getQuery(): Builder
    {
        return parent::getQuery();
    }

    public function getAll(array $filterData = []): Paginator
    {
        $filter = $this->getFilterData($filterData);
        $query = $this->getInstituteQuery();

        if (! $filter['with_deleted']) {
            $query->whereNull("{$this->table}.deleted_at");
        }

        if (isset($filter['search']) && strlen($filter['search']) > 0) {
            $query = $this->filterSearchQuery($query, $filter['search']);
        }

        // status=1 -> active only; status=0 -> inactive only. Inactive is stored
        // as 0 by statusUpdate but as 2 by the original schema, so "not active"
        // is expressed as `!= 1` rather than `= 0`.
        if (isset($filter['status']) && $filter['status'] !== '' && $filter['status'] !== null) {
            (int) $filter['status'] === 1
                ? $query->where("{$this->table}.status", 1)
                : $query->where("{$this->table}.status", '!=', 1);
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
            'status' => '',
            'orderBy' => 'id',
            'order' => 'desc',
            'with_deleted' => false,
        ];

        return array_merge($defaultArgs, $filterData);
    }

    private function getInstituteQuery(): EloquentBuilder
    {
        return Institute::query()
            ->withCount('branches')
            ->with(['branches'])
            ->leftJoin('users as owner', 'institutes.owner_id', '=', 'owner.id')
            ->leftJoin('users as assigned', 'institutes.assigned_to', '=', 'assigned.id')
            // Join only the institute's most-recent subscription. A plain join on
            // subscriptions.institute_id duplicated the institute row (and
            // inflated branches_count) once a tenant had any subscription history.
            ->leftJoinSub(
                DB::table('subscriptions')
                    ->select('institute_id', DB::raw('MAX(id) as id'))
                    ->whereNull('deleted_at')
                    ->groupBy('institute_id'),
                'latest_sub',
                'latest_sub.institute_id',
                '=',
                'institutes.id'
            )
            ->leftJoin('subscriptions', 'subscriptions.id', '=', 'latest_sub.id')
            ->leftJoin('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->select([
                'institutes.*',
                'owner.name as owner_name',
                'assigned.name as assigned_name',
                'plans.id as plan_id',
                'plans.name as plan_name',
                'plans.price as plan_price',
                'subscriptions.id as subscription_id',
                'subscriptions.plan_id',
                'subscriptions.start_date',
                'subscriptions.end_date',
                'subscriptions.status as subscription_status',
            ]);
    }

    protected function filterSearchQuery(Builder|EloquentBuilder $query, string $searchedText): Builder
    {
        $searchable = "%$searchedText%";

        // Grouped: an ungrouped orWhere chain escapes the deleted_at / status
        // conditions applied by the caller (… AND deleted_at IS NULL OR email LIKE …),
        // which let deleted and filtered-out rows resurface in search results.
        return $query->where(function ($query) use ($searchable) {
            $query->where('institutes.name', 'LIKE', $searchable)
                ->orWhere('institutes.email', 'LIKE', $searchable)
                ->orWhere('institutes.institute_type', 'LIKE', $searchable)
                ->orWhere('institutes.phone', 'LIKE', $searchable)
                ->orWhere('institutes.domain', 'LIKE', $searchable);
        });
    }

    /**
     * @throws Exception
     */
    public function getByColumn(string $columnName, $columnValue, array $selects = ['*']): ?object
    {
        $user = $this->getInstituteQuery()
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
            $user = Institute::find($userId);

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
            $data['assigned_to'] = $this->getCurrentUserId();
            $data['created_by'] = $this->getCurrentUserId();
            $data['status'] = 1;
            if (! empty($data['logo']) && $data['logo'] instanceof UploadedFile) {
                $data['logo'] = fileUploader('institute/', 'png', $data['logo']);
            }
        } else {
            if (! empty($data['logo']) && $data['logo'] instanceof UploadedFile) {
                $data['logo'] = fileUploader('institute/', 'png', $data['logo'], $item->logo);
            }
            $data['updated_at'] = now();
        }

        return $data;
    }

    /**
     * @throws Exception
     */
    public function update(int $id, array $data): ?object
    {
        try {
            $institute = Institute::find($id);
            $data = $this->prepareForDB($data, $institute);
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
            static::MESSAGE_ITEM_DOES_NOT_EXIST_MESSAGE => 'Institute does not exist.',
            static::MESSAGE_ITEM_COULD_NOT_BE_DELETED => 'Institute could not be deleted.',
        ];

        return array_merge($exceptionMessages, $userExceptionMessages);
    }
}
