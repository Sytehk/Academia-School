<?php

namespace Modules\Payroll\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Academic\Services\UserService;
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

    public function index(): JsonResponse
    {
        // Get common data: users, salary heads, and user payroll data
        [$users, $salaryHeads, $salaryHeadUserPayrolls] = $this->commonData();

        // Convert $salaryHeadUserPayrolls to a collection if it is not already
        $salaryHeadUserPayrolls = collect($salaryHeadUserPayrolls);

        // Map each user with their salary heads and payrolls
        $mappedData = $users->map(function ($user) use ($salaryHeads, $salaryHeadUserPayrolls) {
            $userPayrolls = $salaryHeadUserPayrolls->where('user_id', $user->id);

            return [
                'user' => $user,
                'salary_heads' => $salaryHeads->values(),
                'salary_head_user_payrolls' => $userPayrolls->values(),
            ];
        })->values();

        return $this->responseSuccess(
            $mappedData,
            _lang('Payroll Assign fetch successfully.')
        );
    }

    public function create(Request $request): JsonResponse
    {
        $result = $this->salaryHeadService->updatePayrollsAPI($request->all());

        if (! $result) {
            return $this->responseError([], $result['message']);
        }

        return $this->responseSuccess(
            [],
            _lang($result['message'])
        );
    }

    public function salaryCreateGet(Request $request): JsonResponse
    {
        $year = (int) $request->year;
        $month = $request->month;

        // Get common data: users, salary heads, and user payroll data
        [$users, $salaryHeads, $salaryHeadUserPayrolls] = $this->commonData();

        // Convert $salaryHeadUserPayrolls to a collection if it is not already
        $salaryHeadUserPayrolls = collect($salaryHeadUserPayrolls);

        // Map each user with their salary heads and payrolls
        $mappedData = $users->map(function ($user) use ($salaryHeads, $salaryHeadUserPayrolls) {
            // Filter salary heads and payrolls for the current user
            $userSalaryHeads = $salaryHeads; // Modify if necessary
            $userPayrolls = $salaryHeadUserPayrolls->where('user_id', $user->id); // Now works since it's a collection

            return [
                'user' => $user,
                'salary_heads' => $userSalaryHeads->values(),
                'salary_head_user_payrolls' => $userPayrolls->values(), // Reset keys if needed
            ];
        })->values();

        return $this->responseSuccess(
            [
                'year' => $year,
                'month' => $month,
                'salary_slips' => $mappedData,
            ],
            _lang('Payroll Create fetch successfully.')
        );
    }

    public function salaryCreate(Request $request): JsonResponse
    {
        $result = $this->userPayrollService->createPayslipSalariesAndHeadsAPI($request->all());

        if (! $result) {
            return $this->responseError([], $result['message']);
        }

        return $this->responseSuccess(
            [],
            _lang('Payslip Create successfully.')
        );
    }

    public function userSalaryAssign(Request $request)
    {
        $result = $this->salaryHeadService->storeOrUpdatePayrolls($request->all());

        if (! empty($result['error'])) {
            return $this->responseError([], $result['message']);
        }

        return $this->responseSuccess([], $result['message']);
    }

    public function removeSalaryHead($requestData)
    {
        DB::beginTransaction();

        try {

            $userPayroll = UserPayroll::where('user_id', $requestData['user_id'])->first();

            if (! $userPayroll) {
                throw new \Exception('User payroll not found');
            }

            $salaryHeadPayroll = SalaryHeadUserPayroll::where('user_payroll_id', $userPayroll->id)
                ->where('salary_head_id', $requestData['salary_head_id'])
                ->first();

            if (! $salaryHeadPayroll) {
                throw new \Exception('Salary head not found for this user');
            }

            $salaryHeadPayroll->delete();

            // Recalculate salary
            $addition = SalaryHeadUserPayroll::join('salary_heads', 'salary_heads.id', '=', 'salary_head_user_payrolls.salary_head_id')
                ->where('user_payroll_id', $userPayroll->id)
                ->where('salary_heads.type', 'Addition')
                ->sum('amount');

            $deduction = SalaryHeadUserPayroll::join('salary_heads', 'salary_heads.id', '=', 'salary_head_user_payrolls.salary_head_id')
                ->where('user_payroll_id', $userPayroll->id)
                ->where('salary_heads.type', 'Deduction')
                ->sum('amount');

            $userPayroll->update([
                'net_salary' => $addition - $deduction,
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Salary head removed successfully',
            ];
        } catch (\Exception $e) {

            DB::rollBack();

            return [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function salaryPaymentProcess(Request $request): JsonResponse
    {
        $year = intval($request->year);
        $month = intval($request->month);

        // Get common data: users, salary heads, and user payroll data
        [$users, $salaryHeads, $salaryHeadUserPayrolls] = $this->commonData();

        // Convert $salaryHeadUserPayrolls to a collection if it is not already
        $salaryHeadUserPayrolls = collect($salaryHeadUserPayrolls);

        // Filter users with valid payslipSalary for the given year and month
        $filteredUsers = $users->filter(function ($user) use ($year, $month) {
            return isset($user->payslipSalary) &&
                $user->payslipSalary->some(fn ($payslip) => $payslip->year == $year && $payslip->month == $month && $payslip->is_paid !== '1');
        });

        // Map filtered users with their respective salary heads and payroll data
        $mappedData = $filteredUsers->map(function ($user) use ($salaryHeads, $salaryHeadUserPayrolls) {
            $userPayrollId = $user->userPayroll->id ?? null;

            // Filter salary head user payrolls for the current user
            $userSalaryHeadUserPayrolls = $salaryHeadUserPayrolls->filter(function ($item) use ($userPayrollId) {
                return $item->user_payroll_id === $userPayrollId;
            });

            // Calculate due amount and payable amount
            $currentDue = $user->userPayroll->current_due ?? 0;
            $currentAdvance = $user->userPayroll->current_advance ?? 0;
            $netSalary = $user->userPayroll->net_salary ?? 0;

            $dueAmount = $currentDue - $currentAdvance;
            $payableAmount = $netSalary + $dueAmount;

            return [
                'user' => $user,
                'salary_heads' => $salaryHeads->values(),
                'salary_head_user_payrolls' => $userSalaryHeadUserPayrolls->values(),
                'due_amount' => $dueAmount,
                'net_salary' => $netSalary,
                'payable_amount' => $payableAmount,
                'salary_slips' => $payableAmount,
            ];
        })->values();

        return $this->responseSuccess(
            [
                'year' => $year,
                'month' => $month,
                'salary_slips' => $mappedData->values(),
            ],
            _lang('Payroll Create fetch successfully.')
        );
    }

    public function salaryPayment(Request $request): JsonResponse
    {
        $result = $this->salaryPaymentService->processSalaryPayment($request);

        if (! $result) {
            return $this->responseError([], $result['message']);
        }

        return $this->responseSuccess(
            [],
            _lang('Salary Payment successfully.')
        );
    }

    public function advanceSalaryPayment(Request $request): JsonResponse
    {
        // Optional validation
        $request->validate([
            'user_id' => 'nullable|integer|exists:users,id',
        ]);

        $query = UserPayroll::with(['user'])
            ->where('current_advance', '>', 0);

        // ✅ Apply user_id filter if provided
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $userPayroll = $query->get();

        // ✅ Handle if no result found
        if ($userPayroll->isEmpty()) {
            return $this->responseSuccess([], _lang('No advance salary users found.'));
        }

        return $this->responseSuccess(
            [
                'userPayroll' => $userPayroll,
            ],
            _lang('Advance salary users fetched successfully.')
        );
    }

    public function advanceSalaryPay(Request $request): JsonResponse
    {
        $result = $this->paymentService->processPayment($request->all());

        return $this->responseSuccess(
            $result,
            _lang('Advance Payment pay successfully.')
        );
    }

    public function dueSalaryPayment(Request $request): JsonResponse
    {
        $query = UserPayroll::with(['user'])
            ->where('user_payrolls.current_due', '>', 0);

        // ✅ Optional user filter
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $userPayroll = $query->get();

        // ✅ If no result found
        if ($userPayroll->isEmpty()) {
            return $this->responseSuccess([], _lang('No due salary users found.'));
        }

        return $this->responseSuccess(
            [
                'userPayroll' => $userPayroll,
            ],
            _lang('Due salary users fetched successfully.')
        );
    }

    public function dueSalaryPay(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:advanced,due,advanced_return',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:1',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'note' => 'nullable|string',
        ]);

        $result = $this->paymentService->processPayment($request->all());

        if (! $result['success']) {
            return $this->responseError([], $result['message']);
        }

        return $this->responseSuccess(
            $result,
            _lang('Due payment processed successfully.')
        );
    }

    public function returnSalaryPayment(Request $request): JsonResponse
    {
        $query = UserPayroll::with('user')
            ->where('current_due', '>', 0)
            ->where('current_advance', '>', 0);

        // Optional user filter
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $userPayroll = $query->get();

        if ($userPayroll->isEmpty()) {
            return $this->responseSuccess([], _lang('No staff found with both due and advance salary.'));
        }

        return $this->responseSuccess(
            [
                'userPayroll' => $userPayroll,
            ],
            _lang('Staff found with both due and advance salary.')
        );
    }

    public function returnSalaryPay(Request $request): JsonResponse
    {
        $result = $this->paymentService->processPayment($request->all());

        return $this->responseSuccess(
            $result,
            _lang('Return payment successfully.')
        );
    }

    public function mapping(): JsonResponse
    {
        $data = PayrollAccountingMapping::with('ledger', 'fund')
            ->where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->get();

        return $this->responseSuccess(
            $data,
            _lang('Payroll Mapping data fetch successfully.')
        );
    }

    public function mappingStore(Request $request): JsonResponse
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

            return $this->responseSuccess(
                $mapping,
                _lang('Payroll Configured.')
            );
        } else {
            return $this->responseError([], _lang('Something Went Wrong'));
        }
    }

    public function staffSalaryConfig(): JsonResponse
    {
        [$users, $salaryHeads, $salaryHeadUserPayrolls] = $this->commonData();

        return $this->responseSuccess(
            [
                'users' => $users,
                'salary_heads' => $salaryHeads,
                'salary_head_user_payrolls' => $salaryHeadUserPayrolls,
            ],
            _lang('Staff Salary config successfully.')
        );
    }

    public function staffSalaryConfigCreate(Request $request): JsonResponse
    {
        $salaryHeads = $request->salary_heads;

        foreach ($request->staff_ids as $staffId) {
            if ($request->has('net_salary')) {
                $netSalary = $request->net_salary;
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

        return $this->responseSuccess(
            [],
            _lang('Payroll Configured.')
        );
    }

    private function commonData(): array
    {
        $users = $this->userService->getUsersByExcludedType(self::USER_TYPE);

        $salaryHeadUserPayrolls = $this->salaryHeadService
            ->getSalaryHeadUserPayrollsForUsers($users->pluck('id')->toArray());

        $salaryHeadUserPayrolls = collect($salaryHeadUserPayrolls);

        // ✅ only users who have payroll data
        $filteredUserIds = $salaryHeadUserPayrolls->pluck('user_id')->unique();

        $users = $users->whereIn('id', $filteredUserIds);

        $salaryHeads = $this->salaryHeadService->getSalaryHeads();

        return [$users, $salaryHeads, $salaryHeadUserPayrolls];
    }
}
