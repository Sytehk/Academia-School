<?php

declare(strict_types=1);

namespace Modules\Library\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Modules\Academic\Models\LibraryMember;

class LibraryMemberRepository implements BaseRepositoryInterface
{
    /**
     * Get categories by filtering args.
     */
    public function get(array $args = []): Collection|Builder
    {
        $orderBy = empty($args['order_by']) ? 'id' : $args['order_by']; // column name
        $order = empty($args['order']) ? 'asc' : $args['order']; // asc, desc
        $query = LibraryMember::with('user')->orderBy($orderBy, $order);

        if (isset($args['is_query']) && $args['is_query']) {
            return $query;
        }

        return $query->get();
    }

    public function all(): Collection
    {
        return LibraryMember::all();
    }

    public function paginate(int $perPage = 100, array $filter = []): LengthAwarePaginator
    {
        $query = LibraryMember::query();

        return $query->orderBy('id', 'asc')->where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->paginate($perPage);
    }

    public function create(array $data): ?LibraryMember
    {
        return LibraryMember::create($data);
    }

    public function update(array $data, int $id): mixed
    {
        return LibraryMember::find($id)->update($data);
    }

    public function delete(int $id): int
    {
        return LibraryMember::destroy($id);
    }

    public function show(int $id): ?LibraryMember
    {
        return LibraryMember::find($id);
    }
}
