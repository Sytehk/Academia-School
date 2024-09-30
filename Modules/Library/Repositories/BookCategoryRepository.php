<?php

declare(strict_types=1);

namespace Modules\Library\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Library\Models\BookCategory;

class BookCategoryRepository implements BaseRepositoryInterface
{
    public function all(): Collection
    {
        return BookCategory::where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->get();
    }

    public function paginate(int $perPage = 100, array $filter = []): LengthAwarePaginator
    {
        $query = BookCategory::query();

        return $query->orderBy('id', 'asc')->where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->paginate($perPage);
    }

    public function create(array $data): ?BookCategory
    {
        return BookCategory::create($data);
    }

    public function update(array $data, int $id): mixed
    {
        return BookCategory::find($id)->update($data);
    }

    public function delete(int $id): int
    {
        return BookCategory::destroy($id);
    }

    public function show(int $id): ?BookCategory
    {
        return BookCategory::find($id);
    }
}
