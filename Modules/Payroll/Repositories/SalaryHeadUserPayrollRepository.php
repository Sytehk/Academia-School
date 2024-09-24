<?php

declare(strict_types=1);

namespace Modules\Payroll\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Payroll\Models\SalaryHeadUserPayroll;

class SalaryHeadUserPayrollRepository implements BaseRepositoryInterface
{
    public function all(): Collection
    {
        return SalaryHeadUserPayroll::all();
    }

    public function paginate(int $perPage = 100, array $filter = []): LengthAwarePaginator
    {
        $query = SalaryHeadUserPayroll::query();

        return $query->where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->paginate($perPage);
    }

    public function create(array $data): ?SalaryHeadUserPayroll
    {
        return SalaryHeadUserPayroll::create($data);
    }

    public function update(array $data, int $id): mixed
    {
        return SalaryHeadUserPayroll::find($id)->update($data);
    }

    public function delete(int $id): int
    {
        return SalaryHeadUserPayroll::destroy($id);
    }

    public function show(int $id): ?SalaryHeadUserPayroll
    {
        return SalaryHeadUserPayroll::find($id);
    }
}
