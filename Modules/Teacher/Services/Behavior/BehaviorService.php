<?php

declare(strict_types=1);

namespace Modules\Teacher\Services\Behavior;

use App\Interfaces\BaseInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Modules\Teacher\Models\Behavior;

final class BehaviorService implements BaseInterface
{
    public function __construct(
        protected Behavior $model
    ) {}

    /**
     * Get categories by filtering args.
     */
    public function get(array $args = []): Collection|Builder
    {
        $orderBy = empty($args['order_by']) ? 'id' : $args['order_by']; // column name
        $order = empty($args['order']) ? 'asc' : $args['order']; // asc, desc
        $query = Behavior::where('institute_id', get_institute_id())
            ->with([
                'student',
                'class',
                'section',
            ])->orderBy($orderBy, $order);

        if (isset($args['is_query']) && $args['is_query']) {
            return $query;
        }

        return $query->get();
    }

    public function index($request, int $per_page = 10): LengthAwarePaginator
    {
        $orderColumn = request('sort_column', 'id');
        $orderDirection = request('sort_direction', 'desc');

        if (! in_array($orderColumn, ['id', 'name', 'created_at'])) {
            $orderColumn = 'id';
        }
        if (! in_array($orderDirection, ['asc', 'desc'])) {
            $orderDirection = 'desc';
        }

        return $this->model::query()
            ->when($request->search, function ($query) use ($request) {
                $query->where('name', 'like', $request->search.'%');
            })
            ->orderBy($orderColumn, $orderDirection)
            ->paginate($per_page);
    }

    public function getAll($request): Collection
    {
        $orderColumn = request('sort_column', 'id');
        $orderDirection = request('sort_direction', 'desc');

        if (! in_array($orderColumn, ['id', 'name', 'created_at'])) {
            $orderColumn = 'id';
        }
        if (! in_array($orderDirection, ['asc', 'desc'])) {
            $orderDirection = 'desc';
        }

        return $this->model::query()
            ->when($request->search, function ($query) use ($request) {
                $query->where('name', 'like', $request->search.'%');
            })
            ->orderBy($orderColumn, $orderDirection)
            ->get();
    }

    public function getById(int $id): ?Behavior
    {
        $record = $this->model::find($id);

        return $record ?? null;
    }

    public function create(array $data): Behavior
    {
        $data['institute_id'] = get_institute_id();
        $data['branch_id'] = get_branch_id();

        return $this->model::create($data);
    }

    public function update(int $id, array $data): ?Behavior
    {
        $model = $this->model::where('id', $id)->first();
        if (! $model) {
            return null;
        }
        $model->update($data);

        return $model;
    }

    public function delete(int $id): int
    {
        $model = $this->model::where('id', $id)->first();
        if (! $model) {
            return 0;
        }

        return (int) $model->delete();
    }
}
