<?php

namespace Modules\Transport\Repositories;

use App\Abstracts\EntityRepository;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Modules\Transport\Models\TransportMember;
use Symfony\Component\HttpFoundation\Response;

class TransportMemberRepository extends EntityRepository
{
    public string $table = TransportMember::TABLE_NAME;

    protected array $fillableColumns = [
        'institute_id',
        'branch_id',
        'student_id',
        'assigned_route_id',
        'assigned_stop_id',
        'seat_id',
        'transport_id',
        'fare_amount',
        'status',
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
        $query = $this->getTransportMemberQuery();

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

    private function getTransportMemberQuery(): Builder
    {
        return $this->getQuery()

            ->leftJoin(
                'students',
                'students.id',
                '=',
                'transport_members.student_id'
            )

            ->leftJoin(
                'bus_routes',
                'bus_routes.id',
                '=',
                'transport_members.assigned_route_id'
            )

            ->leftJoin(
                'bus_stops',
                'bus_stops.id',
                '=',
                'transport_members.assigned_stop_id'
            )

            ->select(

                'transport_members.id',

                'transport_members.student_id',

                'transport_members.assigned_route_id',

                'transport_members.assigned_stop_id',

                'transport_members.fare_amount',

                'transport_members.status',

                'students.first_name',
                'students.last_name',

                'bus_routes.route_name',

                'bus_stops.stop_name'

            );
    }

    protected function filterSearchQuery(Builder|EloquentBuilder $query, string $searchedText): Builder
    {
        $searchable = "%$searchedText%";

        return $query->where('transport_members.student_id', 'LIKE', $searchable)
            ->orWhere('transport_members.assigned_route_id', 'LIKE', $searchable)
            ->orWhere('transport_members.assigned_stop_id', 'LIKE', $searchable);
    }

    /**
     * @throws Exception
     */
    public function getByColumn(string $columnName, $columnValue, array $selects = ['*']): ?object
    {
        $user = $this->getTransportMemberQuery()
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
            $user = TransportMember::find($userId);

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
            $data['institute_id'] = $this->getCurrentInstituteId();
            $data['branch_id'] = $this->getCurrentBranchId();
            $data['status'] = 1;
            $data['transport_id'] = 'TRN'.strtoupper(uniqid());
        } else {
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
            $user = TransportMember::find($id);
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
            static::MESSAGE_ITEM_DOES_NOT_EXIST_MESSAGE => 'TransportMember does not exist.',
            static::MESSAGE_ITEM_COULD_NOT_BE_DELETED => 'TransportMember could not be deleted.',
        ];

        return array_merge($exceptionMessages, $userExceptionMessages);
    }
}
