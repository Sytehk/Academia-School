<?php

declare(strict_types=1);

namespace Modules\Academic\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Academic\Models\LeaveTypes;

class LeaveTypesRepository implements BaseRepositoryInterface
{
    public function all(): Collection
    {
        return LeaveTypes::where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->get();
    }

    public function paginate(int $perPage = 100, array $filter = []): LengthAwarePaginator
    {
        return LeaveTypes::where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->paginate($perPage);
    }

    public function create(array $data): LeaveTypes
    {
        return LeaveTypes::create($data);
    }

    public function update(array $data, int $id): mixed
    {
        return LeaveTypes::find($id)->update($data);
    }

    public function delete(int $id): int
    {
        return LeaveTypes::destroy($id);
    }

    public function show(int $id): ?LeaveTypes
    {
        return LeaveTypes::find($id);
    }
}
