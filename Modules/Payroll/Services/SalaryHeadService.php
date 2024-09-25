<?php

declare(strict_types=1);

namespace Modules\Payroll\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\Payroll\Models\SalaryHead;
use Modules\Payroll\Models\SalaryHeadUserPayroll;
use Modules\Payroll\Models\UserPayroll;
use Modules\Payroll\Repositories\SalaryHeadRepository;
use Modules\Payroll\Repositories\SalaryHeadUserPayrollRepository;
use Modules\Payroll\Repositories\UserPayrollRepository;

class SalaryHeadService
{
    public function __construct(
        private readonly SalaryHeadRepository $salaryHeadRepository,
        private readonly SalaryHeadUserPayrollRepository $salaryHeadUserPayrollRepository,
        private readonly UserPayrollRepository $userPayrollRepository,

    ) {}

    public function getSalaryHeads(array $filter = [], int $perPage = 100): LengthAwarePaginator
    {
        return $this->salaryHeadRepository->paginate($perPage, $filter);
    }

    public function findSalaryHeadById(int $id): ?SalaryHead
    {
        return $this->salaryHeadRepository->show($id);
    }

    public function createSalaryHead(array $data): ?SalaryHead
    {
        $data['institute_id'] = get_institute_id();
        $data['branch_id'] = get_branch_id();

        return $this->salaryHeadRepository->create($data);
    }

    public function updateSalaryHead(array $data, int $id): mixed
    {
        return $this->salaryHeadRepository->update($data, $id);
    }

    public function deleteSalaryHeadById(int $id): int
    {
        return $this->salaryHeadRepository->delete($id);
    }

    public function findSalaryHeadUserPayrollById(int $id): ?SalaryHeadUserPayroll
    {
        return $this->salaryHeadUserPayrollRepository->show($id);
    }

    public function findUserPayrollById(int $id): ?UserPayroll
    {
        return $this->userPayrollRepository->show($id);
    }

    public function getSalaryHeadUserPayrollsForUsers(array $userIds)
    {
        $instituteId = get_institute_id();
        $branchId = get_branch_id();

        return DB::table('salary_head_user_payrolls')
            ->join('user_payrolls', 'salary_head_user_payrolls.user_payroll_id', '=', 'user_payrolls.id')
            ->where('user_payrolls.institute_id', $instituteId)
            ->where('user_payrolls.branch_id', $branchId)
            ->whereIn('user_payrolls.user_id', $userIds)
            ->get()
            ->toArray();
    }

    public function updatePayrolls($requestData)
    {
        // Start a database transaction to ensure atomicity
        DB::beginTransaction();

        try {
            foreach ($requestData['users'] as $userId => $userData) {
                $totalAdditionNetSalary = 0;
                $totalDeductionNetSalary = 0;

                // Loop through each salary head for the user
                foreach ($userData['salary_heads'] as $salaryHeadId => $salaryHeadAmount) {
                    $userPayrollId = UserPayroll::where('user_id', $userId)->first()->id;

                    // Find the salary head user payroll record
                    $salaryHeadUserPayroll = $this->findSalaryHeadUserPayrollByUserAndHead($userPayrollId, $salaryHeadId);

                    if (! $salaryHeadUserPayroll) {
                        throw new \Exception("Salary Head User Payroll for User ID {$userId} and Salary Head ID {$salaryHeadId} not found.");
                    }

                    // Update the salary head amount
                    $salaryHeadUserPayroll->amount = $salaryHeadAmount;

                    // Get the salary head details
                    $salaryHead = $this->findSalaryHeadById($salaryHeadId);

                    if (! $salaryHead) {
                        throw new \Exception("Salary Head with ID {$salaryHeadId} not found.");
                    }

                    // Update the net salary calculations
                    if ($salaryHead->type == 'Addition') {
                        $totalAdditionNetSalary += $salaryHeadAmount;
                    } elseif ($salaryHead->type == 'Deduction') {
                        $totalDeductionNetSalary += $salaryHeadAmount;
                    }

                    // Save the updated salary head user payroll
                    $salaryHeadUserPayroll->save();
                }

                // Calculate the total net salary
                $totalNetSalary = $totalAdditionNetSalary - $totalDeductionNetSalary;

                // Update the user's payroll record
                $userPayroll = $this->findUserPayrollByUserId($userId);

                if (! $userPayroll) {
                    throw new \Exception("User Payroll for User ID {$userId} not found.");
                }

                $userPayroll->net_salary = $totalNetSalary;
                $userPayroll->save();
            }

            // Commit the transaction
            DB::commit();

            return ['success' => true, 'message' => 'Payrolls updated successfully.'];
        } catch (\Exception $e) {
            // Rollback the transaction in case of errors
            DB::rollBack();

            return ['error' => true, 'message' => 'Payroll update failed: '.$e->getMessage()];
        }
    }

    public function updatePayrollsAPI($requestData)
    {
        // Start a database transaction to ensure atomicity
        DB::beginTransaction();

        try {
            foreach ($requestData['users'] as $user) {
                $userId = $user['user_id'];
                $totalAdditionNetSalary = 0;
                $totalDeductionNetSalary = 0;

                // Retrieve the user's payroll record
                $userPayroll = $this->findUserPayrollByUserId($userId);

                if (! $userPayroll) {
                    throw new \Exception("User Payroll for User ID {$userId} not found.");
                }

                // Loop through each salary head for the user
                foreach ($user['salary_heads'] as $salaryHead) {
                    $salaryHeadId = $salaryHead['salary_head_id'];
                    $salaryHeadAmount = (float) $salaryHead['amount'];

                    // Find the salary head user payroll record
                    $salaryHeadUserPayroll = $this->findSalaryHeadUserPayrollByUserAndHead($userPayroll->id, $salaryHeadId);

                    if (! $salaryHeadUserPayroll) {
                        throw new \Exception("Salary Head User Payroll for User ID {$userId} and Salary Head ID {$salaryHeadId} not found.");
                    }

                    // Update the salary head amount
                    $salaryHeadUserPayroll->amount = $salaryHeadAmount;

                    // Get the salary head details
                    $salaryHeadDetails = $this->findSalaryHeadById($salaryHeadId);

                    if (! $salaryHeadDetails) {
                        throw new \Exception("Salary Head with ID {$salaryHeadId} not found.");
                    }

                    // Update the net salary calculations
                    if ($salaryHeadDetails->type === 'Addition') {
                        $totalAdditionNetSalary += $salaryHeadAmount;
                    } elseif ($salaryHeadDetails->type === 'Deduction') {
                        $totalDeductionNetSalary += $salaryHeadAmount;
                    }

                    // Save the updated salary head user payroll
                    $salaryHeadUserPayroll->save();
                }

                // Calculate the total net salary
                $totalNetSalary = $totalAdditionNetSalary - $totalDeductionNetSalary;

                // Update the user's payroll record
                $userPayroll->net_salary = $totalNetSalary;
                $userPayroll->save();
            }

            // Commit the transaction
            DB::commit();

            return [
                'success' => true,
                'message' => 'Payrolls updated successfully.',
            ];
        } catch (\Exception $e) {
            // Rollback the transaction in case of errors
            DB::rollBack();

            return [
                'error' => true,
                'message' => 'Payroll update failed: '.$e->getMessage(),
            ];
        }
    }

    private function findSalaryHeadUserPayrollByUserAndHead($userId, $salaryHeadId)
    {
        return SalaryHeadUserPayroll::where('user_payroll_id', $userId)
            ->where('salary_head_id', $salaryHeadId)
            ->first();
    }

    private function findUserPayrollByUserId($userId)
    {
        return UserPayroll::where('user_id', $userId)->first();
    }

    public function storeOrUpdatePayrolls($requestData)
    {
        DB::beginTransaction();

        try {
            foreach ($requestData['users'] as $user) {
                $userId = $user['user_id'];
                $totalAddition = 0;
                $totalDeduction = 0;

                // create or get payroll
                $userPayroll = UserPayroll::firstOrCreate([
                    'user_id' => $userId,
                ]);

                foreach ($user['salary_heads'] as $salaryHead) {

                    $salaryHeadId = $salaryHead['salary_head_id'];
                    $amount = (float) $salaryHead['amount'];

                    $salaryHeadDetails = SalaryHead::find($salaryHeadId);

                    if (! $salaryHeadDetails) {
                        throw new \Exception("Salary Head {$salaryHeadId} not found");
                    }

                    SalaryHeadUserPayroll::updateOrCreate(
                        [
                            'institute_id' => get_institute_id(),
                            'branch_id' => get_branch_id(),
                            'user_payroll_id' => $userPayroll->id,
                            'salary_head_id' => $salaryHeadId,
                        ],
                        [
                            'amount' => $amount,
                        ]
                    );

                    if ($salaryHeadDetails->type == 'Addition') {
                        $totalAddition += $amount;
                    }

                    if ($salaryHeadDetails->type == 'Deduction') {
                        $totalDeduction += $amount;
                    }
                }

                $netSalary = $totalAddition - $totalDeduction;

                $userPayroll->update([
                    'net_salary' => $netSalary,
                ]);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Salary sheet created or updated successfully',
            ];
        } catch (\Exception $e) {

            DB::rollBack();

            return [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }
    }
}
