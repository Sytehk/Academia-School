<?php

declare(strict_types=1);

namespace Modules\Payroll\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Payroll\Models\PayslipInvoice;
use Modules\Payroll\Repositories\PayslipInvoiceRepository;

class PayslipInvoiceService
{
    public function __construct(
        private readonly PayslipInvoiceRepository $payslipInvoiceRepository

    ) {}

    public function getPayslipInvoices(array $filter = [], int $perPage = 100): LengthAwarePaginator
    {
        return $this->payslipInvoiceRepository->paginate($perPage, $filter);
    }

    public function findPayslipInvoiceById(int $id): ?PayslipInvoice
    {
        return $this->payslipInvoiceRepository->show($id);
    }

    public function createPayslipInvoice(array $data): ?PayslipInvoice
    {
        $data['institute_id'] = get_institute_id();
        $data['branch_id'] = get_branch_id();

        return $this->payslipInvoiceRepository->create($data);
    }

    public function updatePayslipInvoice(array $data, int $id): mixed
    {
        return $this->payslipInvoiceRepository->update($data, $id);
    }

    public function deletePayslipInvoiceById(int $id): int
    {
        return $this->payslipInvoiceRepository->delete($id);
    }
}
