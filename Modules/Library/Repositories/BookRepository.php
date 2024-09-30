<?php

declare(strict_types=1);

namespace Modules\Library\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Modules\Library\Models\Book;

class BookRepository implements BaseRepositoryInterface
{
    /**
     * Get categories by filtering args.
     */
    public function get(array $args = []): Collection|Builder
    {
        $orderBy = empty($args['order_by']) ? 'id' : $args['order_by']; // column name
        $order = empty($args['order']) ? 'asc' : $args['order']; // asc, desc
        $query = Book::where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->orderBy($orderBy, $order);

        if (isset($args['is_query']) && $args['is_query']) {
            return $query;
        }

        return $query->get();
    }

    public function all(): Collection
    {
        return Book::where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->get();
    }

    public function paginate(int $perPage = 100, array $filter = []): LengthAwarePaginator
    {
        $query = Book::query();

        return $query->orderBy('id', 'asc')->where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->paginate($perPage);
    }

    public function create(array $data): ?Book
    {
        return Book::create($data);
    }

    public function update(array $data, int $id): mixed
    {
        return Book::find($id)->update($data);
    }

    public function delete(int $id): int
    {
        return Book::destroy($id);
    }

    public function show(int $id): ?Book
    {
        return Book::find($id);
    }
}
