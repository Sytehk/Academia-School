<?php

declare(strict_types=1);

namespace Modules\Finance\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Finance\Models\FeeHead;

class FeeHeadRepository implements BaseRepositoryInterface
{
    public function all(): Collection
    {
        return FeeHead::where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->get();
    }

    public function paginate(int $perPage = 100, array $filter = []): LengthAwarePaginator
    {
        $query = FeeHead::query();

        return $query->where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->orderBy('serial', 'asc')->paginate(5000);
    }

    public function create(array $data): ?FeeHead
    {
        return FeeHead::create($data);
    }

    public function update(array $data, int $id): mixed
    {
        return FeeHead::find($id)->update($data);
    }

    public function delete(int $id): int
    {
        return FeeHead::destroy($id);
    }

    public function show(int $id): ?FeeHead
    {
        return FeeHead::find($id);
    }
}
