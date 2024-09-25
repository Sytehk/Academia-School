<?php

declare(strict_types=1);

namespace Modules\Payroll\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Services\AccountTransactionService;
use Modules\Payroll\Models\PayslipSalary;
use Modules\Payroll\Repositories\PayslipSalaryRepository;

class PayslipSalaryService
{
    public function __construct(
        private readonly PayslipSalaryRepository $payslipSalaryRepository,
        private readonly PaymentService $paymentService,
        private readonly UserPayrollService $userPayrollService,
        private readonly AccountTransactionService $accountTransactionService,

    ) {}

    public function processSalaryPayment(Request $request)
    {
        $year = (int) $request->year;
        $month = $request->month;
        $userIds = $request->user_id;
        $payableAmounts = $request->payable;
        $paidAmounts = $request->paid_amount;

        DB::beginTransaction();
        try {
            foreach ($userIds as $key => $userId) {
                $existingPayslipSalary = PayslipSalary::where([
                    'user_id' => (int) $userId,
                    'year' => $year,
                    'month' => $month,
                ])->first();
                if (! $existingPayslipSalary) {
                    continue;
                }

                $paidAmount = floatval($paidAmounts[$key]);
                if ($existingPayslipSalary->is_paid != '1') {
                    $paymentData = [
                        'institute_id' => get_institute_id(),
                        'branch_id' => get_branch_id(),
                        'user_id' => (int) $userId,
                        'year' => $year,
                        'month' => $month,
                        'amount' => $paidAmount,
                        'type' => 'salary',
                        'paid_by' => Auth::id(),
                    ];
                    $this->accountTransactionService->prepareForAccTransAndDetails($paymentData, 'debit');
                    $this->paymentService->createPayment($paymentData);
                }

                $existingPayslipSalary->paid_amount = $paidAmount;
                $existingPayslipSalary->is_paid = $paidAmount > 0 ? '1' : '0';
                $existingPayslipSalary->payment_date = now();
                $existingPayslipSalary->save();

                $payableAmount = floatval($payableAmounts[$key]);
                $dueAmount = $payableAmount - $paidAmount;
                $currentDueAmount = ($dueAmount > 0) ? $dueAmount : 0;
                $advanceAmount = ($dueAmount > 0) ? 0 : abs($dueAmount);
                $userPayroll = $this->userPayrollService->findByUserId((int) $userId);
                $userPayroll->current_due = $currentDueAmount;
                $userPayroll->current_advance = $advanceAmount;
                $userPayroll->save();

                if ($advanceAmount != 0) {
                    $paymentData = [
                        'institute_id' => get_institute_id(),
                        'branch_id' => get_branch_id(),
                        'user_id' => (int) $userId,
                        'year' => $year,
                        'month' => $month,
                        'amount' => $advanceAmount,
                        'type' => 'advanced',
                        'paid_by' => Auth::id(),
                    ];
                    $this->accountTransactionService->prepareForAccTransAndDetails($paymentData, 'debit');
                    $this->paymentService->createPayment($paymentData);
                } else {
                    $type = ($currentDueAmount > 0) ? 'due' : 'advanced';
                    if ($currentDueAmount != 0) {
                        $paymentData = [
                            'institute_id' => get_institute_id(),
                            'branch_id' => get_branch_id(),
                            'user_id' => (int) $userId,
                            'year' => $year,
                            'month' => $month,
                            'amount' => $currentDueAmount,
                            'type' => $type,
                            'paid_by' => Auth::id(),
                        ];

                        // Transaction Data
                        $this->accountTransactionService->prepareForAccTransAndDetails($paymentData, 'credit');
                        $this->paymentService->createPayment($paymentData);
                    }
                }
            }

            DB::commit();

            return ['success' => true, 'message' => 'Salary payment processed successfully.'];
        } catch (\Exception $e) {
            DB::rollback();

            return ['error' => true, 'message' => 'Salary payment processed unsuccessfully'];
        }
    }
}
