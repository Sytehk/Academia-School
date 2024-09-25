<?php

namespace Modules\Payroll\Http\Controllers\WEB;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Academic\Services\UserService;
use Modules\Accounting\Models\AccountingLedger;
use Modules\Payroll\Models\PayrollAccountingMapping;
use Modules\Payroll\Models\SalaryHeadUserPayroll;
use Modules\Payroll\Models\UserPayroll;
use Modules\Payroll\Services\PaymentService;
use Modules\Payroll\Services\PayslipSalaryService;
use Modules\Payroll\Services\SalaryHeadService;
use Modules\Payroll\Services\UserPayrollService;

class PayrollController extends Controller
{
    public const USER_TYPE = 'Student';

    public function __construct(
        private readonly SalaryHeadService $salaryHeadService,
        private readonly UserPayrollService $userPayrollService,
        private readonly UserService $userService,
        private readonly PaymentService $paymentService,
        private readonly PayslipSalaryService $salaryPaymentService,
    ) {}

    public function index(): View|Factory
    {
        [$users, $salaryHeads, $salaryHeadUserPayrolls] = $this->commonData();

        return view('payroll::payroll.users-list', compact('users', 'salaryHeads', 'salaryHeadUserPayrolls'));
    }

    public function create(Request $request): RedirectResponse
    {
        $result = $this->salaryHeadService->updatePayrolls($request->all());

        if (! $result) {
            return back()->with('error', _lang($result['message']));
        }

        return back()->with('success', _lang($result['message']));
    }

    public function salaryCreateGet(Request $request): View|Factory
    {
        $year = $request->input('year');
        $month = $request->input('month');

        [$users, $salaryHeads, $salaryHeadUserPayrolls] = $this->commonData();

        if ($request->isMethod('post')) {
            return view('payroll::payroll.salary-create', compact('users', 'salaryHeads', 'salaryHeadUserPayrolls', 'year', 'month'));
        } else {
            return view('payroll::payroll.salary-create');
        }
    }

    public function salaryCreate(Request $request): RedirectResponse
    {
        $result = $this->userPayrollService->createPayslipSalariesAndHeads($request->all());

        if (! $result) {
            return back()->with('error', _lang($result['message']));
        }

        return back()->with('success', _lang($result['message']));
    }

    public function salaryPaymentProcess(Request $request): View|Factory
    {
        $year = $request->input('year') ?? Carbon::now()->year;
        $month = $request->input('month') ?? Carbon::now()->month;

        [$users, $salaryHeads, $salaryHeadUserPayrolls] = $this->commonData();

        if ($request->isMethod('post')) {
            return view('payroll::payroll.salary-payment', compact('users', 'salaryHeads', 'salaryHeadUserPayrolls', 'year', 'month'));
        } else {
            return view('payroll::payroll.salary-payment', compact('year', 'month'));
        }
    }

    public function salaryPayment(Request $request): RedirectResponse
    {
        $result = $this->salaryPaymentService->processSalaryPayment($request);

        if (! $result) {
            return back()->with('error', _lang($result['message']));
        }

        return back()->with('success', _lang($result['message']));
    }

    public function advanceSalaryPayment(Request $request): View|Factory|RedirectResponse
    {
        if ($request->isMethod('post')) {
            $request->validate([
                'user_id' => 'required',
            ]);

            [$user, $userPayroll] = $this->getUserAndUserPayroll($request->user_id);

            if ($user && $userPayroll) {
                $cashLedgers = AccountingLedger::where('accounting_category_id', 1)->get();

                return view('payroll::payroll.advance-salary-payment', compact('user', 'userPayroll', 'cashLedgers'));
            }

            return back()->with('error', _lang('HR Not Found!'));
        } else {
            return view('payroll::payroll.advance-salary-payment');
        }
    }

    public function advanceSalaryPay(Request $request): RedirectResponse
    {
        $result = $this->paymentService->processPayment($request->all());

        return back()->with($result);
    }

    public function dueSalaryPayment(Request $request): View|Factory|RedirectResponse
    {
        if ($request->isMethod('post')) {
            $request->validate([
                'user_id' => 'required',
            ]);

            [$user, $userPayroll] = $this->getUserAndUserPayroll($request->user_id);

            if ($user && $userPayroll) {
                $cashLedgers = AccountingLedger::where('accounting_category_id', 1)->get();

                return view('payroll::payroll.due-salary-payment', compact('user', 'userPayroll', 'cashLedgers'));
            }

            return back()->with('error', _lang('HR Not Found!'));
        } else {
            return view('payroll::payroll.due-salary-payment');
        }
    }

    public function dueSalaryPay(Request $request): RedirectResponse
    {
        $result = $this->paymentService->processPayment($request->all());

        return back()->with($result);
    }

    public function returnSalaryPayment(Request $request): View|Factory|RedirectResponse
    {
        if ($request->isMethod('post')) {
            $request->validate([
                'user_id' => 'required',
            ]);

            [$user, $userPayroll] = $this->getUserAndUserPayroll($request->user_id);

            if ($user && $userPayroll) {
                $cashLedgers = AccountingLedger::where('accounting_category_id', 1)->get();

                return view('payroll::payroll.return-salary-payment', compact('user', 'userPayroll', 'cashLedgers'));
            }

            return back()->with('error', _lang('HR Not Found!'));
        } else {
            return view('payroll::payroll.return-salary-payment');
        }
    }

    public function returnSalaryPay(Request $request): RedirectResponse
    {
        $result = $this->paymentService->processPayment($request->all());

        return back()->with($result);
    }

    private function commonData()
    {
        $users = $this->userService->getUsersByExcludedType(self::USER_TYPE);
        $salaryHeadUserPayrolls = $this->salaryHeadService->getSalaryHeadUserPayrollsForUsers($users->pluck('id')->toArray());
        $salaryHeads = $this->salaryHeadService->getSalaryHeads();

        return [$users, $salaryHeads, $salaryHeadUserPayrolls];
    }

    private function getUserAndUserPayroll(int $userId)
    {
        $user = $this->userService->findUserById(intval($userId));
        $userPayroll = $this->userPayrollService->findByUserId(intval($userId));

        return [$user, $userPayroll];
    }

    public function mapping()
    {
        $data = PayrollAccountingMapping::where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->get();

        return view('payroll::payroll.payroll-mapping', compact('data'));
    }

    public function mappingStore(Request $request)
    {
        if ($request->ledger_id != null && $request->fund_id != null) {
            $mapping = PayrollAccountingMapping::updateOrCreate(
                [
                    'institute_id' => get_institute_id(),
                    'branch_id' => get_branch_id(),
                ],
                [
                    'ledger_id' => $request->ledger_id,
                    'fund_id' => $request->fund_id,
                ]
            );
        }

        if (isset($mapping)) {
            return back()->with('success', _lang('Payroll Configured'));
        } else {
            return back()->with('error', _lang('Something Went Wrong'));
        }
    }

    public function staffSalaryConfig()
    {
        [$users, $salaryHeads, $salaryHeadUserPayrolls] = $this->commonData();

        return view('payroll::payroll.staff-salary-create', compact('users', 'salaryHeads', 'salaryHeadUserPayrolls'));
    }

    public function staffSalaryConfigCreate(Request $request)
    {
        $salaryHeads = $request->input('salary_heads');
        foreach ($request->input('staff_ids') as $staffId) {
            if ($request->has('net_salary')) {
                $netSalary = $request->input('net_salary');
                if (isset($netSalary)) {
                    $userPayroll = UserPayroll::updateOrCreate(
                        ['user_id' => $staffId],
                        ['net_salary' => $netSalary]
                    );

                    if ($userPayroll) {
                        if (isset($salaryHeads[$staffId]) && is_array($salaryHeads[$staffId])) {
                            foreach ($salaryHeads[$staffId] as $salaryHeadId => $amount) {
                                SalaryHeadUserPayroll::updateOrCreate(
                                    [
                                        'user_payroll_id' => $userPayroll->id,
                                        'salary_head_id' => $salaryHeadId,
                                    ],
                                    ['amount' => $amount]
                                );
                            }
                        }
                    }
                }
            }
        }

        return back()->with('success', _lang('Salary Configured inserted or updated successfully'));
    }
}
