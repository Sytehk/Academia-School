<?php

declare(strict_types=1);

namespace Modules\Payroll\Repositories;

use App\Interfaces\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Payroll\Models\PayslipInvoice;

class PayslipInvoiceRepository implements BaseRepositoryInterface
{
    public function all(): Collection
    {
        return PayslipInvoice::all();
    }

    public function paginate(int $perPage = 100, array $filter = []): LengthAwarePaginator
    {
        $query = PayslipInvoice::query();

        return $query->where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->paginate($perPage);
    }

    public function create(array $data): ?PayslipInvoice
    {
        return PayslipInvoice::create($data);
    }

    public function update(array $data, int $id): mixed
    {
        return PayslipInvoice::find($id)->update($data);
    }

    public function delete(int $id): int
    {
        return PayslipInvoice::destroy($id);
    }

    public function show(int $id): ?PayslipInvoice
    {
        return PayslipInvoice::find($id);
    }
}
