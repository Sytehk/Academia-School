<?php

declare(strict_types=1);

namespace Modules\Academic\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Academic\Models\Staff;

class StaffRepository implements BaseRepositoryInterface
{
    public function all(): Collection
    {
        return Staff::where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->get();
    }

    public function paginate(int $perPage = 100, array $filter = []): LengthAwarePaginator
    {
        return Staff::where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->paginate($perPage);
    }

    public function create(array $data): ?Staff
    {
        return Staff::create($data);
    }

    public function update(array $data, int $id): mixed
    {
        $staff = $this->show($id);

        return $staff?->update($data) ?? false;
    }

    public function delete(int $id): int
    {
        return Staff::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->where('id', $id)
            ->delete();
    }

    public function show(int $id): ?Staff
    {
        return Staff::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->find($id);
    }
}
