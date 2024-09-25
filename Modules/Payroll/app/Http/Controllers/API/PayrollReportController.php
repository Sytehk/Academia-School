<?php

namespace Modules\Payroll\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Authentication\Models\User;
use Modules\Payroll\Models\UserPayroll;

class PayrollReportController extends Controller
{
    public function salaryStatementReport(Request $request): JsonResponse
    {
        $year = intval($request->year);
        $month = intval($request->month);
        if (! $year || ! $month) {
            return $this->responseError([], 'Invalid year or month.');
        }

        try {
            $data = UserPayroll::select('payslip_salaries.*', 'user_payrolls.*', 'payments.*')
                ->join('payslip_salaries', function ($join) {
                    $join->on('payslip_salaries.user_id', '=', 'user_payrolls.user_id')
                        ->on('payslip_salaries.institute_id', '=', 'user_payrolls.institute_id')
                        ->on('payslip_salaries.branch_id', '=', 'user_payrolls.branch_id');
                })
                ->join('payments', function ($join) {
                    $join->on('payments.user_id', '=', 'user_payrolls.user_id')
                        ->on('payments.institute_id', '=', 'user_payrolls.institute_id')
                        ->on('payments.branch_id', '=', 'user_payrolls.branch_id');
                })
                ->whereYear('payslip_salaries.payment_date', $year)
                ->whereMonth('payslip_salaries.payment_date', $month)
                ->with('user')
                ->get();

            if ($data && count($data) > 0) {
                return $this->responseSuccess($data, _lang('Payroll report successfully.'));
            }

            return $this->responseError([], 'No Data Found', 404);
        } catch (\Exception $e) {
            return $this->responseError([], 'Something went wrong.');
        }
    }

    public function getPaymentInfo(Request $request): JsonResponse
    {
        $fromDate = $request->from_date ?? date('Y-m-01');
        $toDate = $request->to_date ?? date('Y-m-t');
        $instituteId = (int) get_institute_id();
        $branchId = (int) get_branch_id();

        try {
            $fromDate = Carbon::createFromFormat('Y-m-d', $fromDate)->startOfDay();
            $toDate = Carbon::createFromFormat('Y-m-d', $toDate)->endOfDay();
        } catch (\Exception $e) {
            return $this->responseError([], 'Invalid date format.');
        }

        $hrPayments = User::query()
            ->where('institute_id', $instituteId)
            ->where('branch_id', $branchId)
            ->whereIn('user_type', ['Staff', 'Admin', 'Teacher', 'Accountant', 'Librarian', 'Hostel', 'Transport'])
            ->join('user_payrolls', function ($join) use ($instituteId, $branchId) {
                $join->on('users.id', '=', 'user_payrolls.user_id')
                    ->where('user_payrolls.institute_id', $instituteId)
                    ->where('user_payrolls.branch_id', $branchId);
            })
            ->leftJoin('payslip_salaries', function ($join) use ($fromDate, $toDate, $instituteId, $branchId) {
                $join->on('users.id', '=', 'payslip_salaries.user_id')
                    ->where('payslip_salaries.institute_id', $instituteId)
                    ->where('payslip_salaries.branch_id', $branchId)
                    ->whereBetween('payslip_salaries.payment_date', [$fromDate, $toDate]);
            })
            ->leftJoin('payments', function ($join) use ($fromDate, $toDate, $instituteId, $branchId) {
                $join->on('users.id', '=', 'payments.user_id')
                    ->where('payments.institute_id', $instituteId)
                    ->where('payments.branch_id', $branchId)
                    ->whereBetween('payments.created_at', [$fromDate, $toDate]);
            })
            ->leftJoin('payslip_invoices', 'payslip_salaries.id', '=', 'payslip_invoices.payslip_salary_id')
            ->select([
                'users.id as hr_id',
                'users.name as hr_name',
                'payslip_invoices.invoice_id as invoice_id',
                'payslip_salaries.is_paid',
                'payments.type as payment_type',
                'user_payrolls.net_salary',
                DB::raw('user_payrolls.net_salary - IF(payslip_salaries.paid_amount IS NOT NULL, payslip_salaries.paid_amount, 0) as payable_salary'),
                DB::raw('IF(payslip_salaries.paid_amount IS NOT NULL, payslip_salaries.paid_amount, 0) as paid'),
                'user_payrolls.current_due as due',
                'user_payrolls.current_advance as advance',
                'payslip_salaries.payment_date',
            ])
            ->get()
            ->map(fn ($row) => (array) $row->getAttributes());

        if ($hrPayments->isNotEmpty()) {
            return $this->responseSuccess($hrPayments->values(), _lang('HR Payments fetched successfully.'));
        }

        return $this->responseError([], 'No Data Found', 404);
    }
}
