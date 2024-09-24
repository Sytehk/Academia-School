<?php

declare(strict_types=1);

namespace Modules\Payroll\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Payroll\Models\PayslipSalaryHead;

class PayslipSalaryHeadRepository implements BaseRepositoryInterface
{
    public function all(): Collection
    {
        return PayslipSalaryHead::all();
    }

    public function paginate(int $perPage = 100, array $filter = []): LengthAwarePaginator
    {
        $query = PayslipSalaryHead::query();

        // if (isset($filter['search'])) {
        //     $query->where('name', 'like', '%' . $filter['search'] . '%');
        // }

        return $query->where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->paginate($perPage);
    }

    public function create(array $data): ?PayslipSalaryHead
    {
        return PayslipSalaryHead::create($data);
    }

    public function update(array $data, int $id): mixed
    {
        return PayslipSalaryHead::find($id)->update($data);
    }

    public function delete(int $id): int
    {
        return PayslipSalaryHead::destroy($id);
    }

    public function show(int $id): ?PayslipSalaryHead
    {
        return PayslipSalaryHead::find($id);
    }
}
