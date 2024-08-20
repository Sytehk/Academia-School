<?php

declare(strict_types=1);

namespace Modules\Finance\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Finance\Models\Waiver;

class WaiverRepository implements BaseRepositoryInterface
{
    public function all(): Collection
    {
        return Waiver::where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->get();
    }

    public function paginate(int $perPage = 100, array $filter = []): LengthAwarePaginator
    {
        $query = Waiver::query();

        return $query->where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->orderBy('id', 'asc')->paginate($perPage);
    }

    public function create(array $data): ?Waiver
    {
        return Waiver::create($data);
    }

    public function update(array $data, int $id): mixed
    {
        return Waiver::find($id)->update($data);
    }

    public function delete(int $id): int
    {
        return Waiver::destroy($id);
    }

    public function show(int $id): ?Waiver
    {
        return Waiver::find($id);
    }
}
