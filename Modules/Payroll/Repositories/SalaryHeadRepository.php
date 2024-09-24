<?php

declare(strict_types=1);

namespace Modules\Payroll\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Payroll\Models\SalaryHead;

class SalaryHeadRepository implements BaseRepositoryInterface
{
    public function all(): Collection
    {
        return SalaryHead::all();
    }

    public function paginate(int $perPage = 100, array $filter = []): LengthAwarePaginator
    {
        $query = SalaryHead::query()->latest();

        return $query->where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->paginate($perPage);
    }

    public function create(array $data): ?SalaryHead
    {
        return SalaryHead::create($data);
    }

    public function update(array $data, int $id): mixed
    {
        return SalaryHead::find($id)->update($data);
    }

    public function delete(int $id): int
    {
        return SalaryHead::destroy($id);
    }

    public function show(int $id): ?SalaryHead
    {
        return SalaryHead::find($id);
    }
}
