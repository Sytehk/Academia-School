<?php

declare(strict_types=1);

namespace Modules\Payroll\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Payroll\Models\PayslipSalaryHead;
use Modules\Payroll\Repositories\PayslipSalaryHeadRepository;

class PayslipSalaryHeadService
{
    public function __construct(
        private readonly PayslipSalaryHeadRepository $payslipSalaryHeadRepository
    ) {}

    public function getPayslipSalaryHeads(array $filter = [], int $perPage = 100): LengthAwarePaginator
    {
        return $this->payslipSalaryHeadRepository->paginate($perPage, $filter);
    }

    public function findPayslipSalaryHeadById(int $id): ?PayslipSalaryHead
    {
        return $this->payslipSalaryHeadRepository->show($id);
    }

    public function createPayslipSalaryHead(array $data): ?PayslipSalaryHead
    {
        $data['institute_id'] = get_institute_id();
        $data['branch_id'] = get_branch_id();

        return $this->payslipSalaryHeadRepository->create($data);
    }

    public function updatePayslipSalaryHead(array $data, int $id): mixed
    {
        return $this->payslipSalaryHeadRepository->update($data, $id);
    }

    public function deletePayslipSalaryHeadById(int $id): int
    {
        return $this->payslipSalaryHeadRepository->delete($id);
    }
}
