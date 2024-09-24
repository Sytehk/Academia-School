<?php

declare(strict_types=1);

namespace Modules\Payroll\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Payroll\Models\UserPayroll;

class UserPayrollRepository implements BaseRepositoryInterface
{
    public function all(): Collection
    {
        return UserPayroll::all();
    }

    public function paginate(int $perPage = 100, array $filter = []): LengthAwarePaginator
    {
        $query = UserPayroll::query();

        return $query->where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->paginate($perPage);
    }

    public function create(array $data): ?UserPayroll
    {
        return UserPayroll::create($data);
    }

    public function update(array $data, int $id): mixed
    {
        return UserPayroll::find($id)->update($data);
    }

    public function delete(int $id): int
    {
        return UserPayroll::destroy($id);
    }

    public function show(int $id): ?UserPayroll
    {
        return UserPayroll::find($id);
    }
}
