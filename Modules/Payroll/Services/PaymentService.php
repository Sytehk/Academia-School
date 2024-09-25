<?php

declare(strict_types=1);

namespace Modules\Payroll\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Services\AccountTransactionService;
use Modules\Payroll\Models\Payment;
use Modules\Payroll\Repositories\PaymentRepository;

class PaymentService
{
    public const ADVANCED = 'advanced';

    public const DUE = 'due';

    public const ADVANCE_RETURN = 'advanced_return';

    public function __construct(
        private readonly PaymentRepository $paymentRepository,
        private readonly UserPayrollService $userPayrollService,
        private readonly AccountTransactionService $accountTransactionService,
    ) {}

    public function getPayments(array $filter = [], int $perPage = 100): LengthAwarePaginator
    {
        return $this->paymentRepository->paginate($perPage, $filter);
    }

    public function findPaymentById(int $id): ?Payment
    {
        return $this->paymentRepository->show($id);
    }

    public function createPayment(array $data): ?Payment
    {
        $data['institute_id'] = get_institute_id();
        $data['branch_id'] = get_branch_id();

        return $this->paymentRepository->create($data);
    }

    public function updatePayment(array $data, int $id): mixed
    {
        return $this->paymentRepository->update($data, $id);
    }

    public function deletePaymentById(int $id): int
    {
        return $this->paymentRepository->delete($id);
    }

    public function processPayment(array $requestData): array
    {
        DB::beginTransaction();

        try {

            $payment = $this->handlePayment($requestData);

            if (! $payment) {
                DB::rollBack();

                return ['success' => false, 'message' => _lang('Payment Failed!')];
            }

            $userPayroll = $this->userPayrollService->findByUserId((int) $requestData['user_id']);

            if (! $userPayroll) {
                DB::rollBack();

                return ['success' => false, 'message' => _lang('Payroll not found!')];
            }

            $amount = (float) $requestData['amount'];

            if ($requestData['type'] === self::ADVANCED) {

                $userPayroll->current_advance += $amount;
            } elseif ($requestData['type'] === self::DUE) {

                if ($amount >= $userPayroll->current_due) {

                    $userPayroll->current_advance += ($amount - $userPayroll->current_due);
                    $userPayroll->current_due = 0;
                } else {

                    $userPayroll->current_due -= $amount;
                }
            } elseif ($requestData['type'] === self::ADVANCE_RETURN) {

                if ($userPayroll->current_advance >= $amount) {

                    $userPayroll->current_advance -= $amount;
                } else {

                    $remainingAmount = $amount - $userPayroll->current_advance;

                    $userPayroll->current_advance = 0;
                    $userPayroll->current_due += $remainingAmount;
                }
            }

            $userPayroll->save();

            DB::commit();

            return [
                'success' => true,
                'message' => _lang(ucfirst($requestData['type']).' payment Successfully!'),
            ];
        } catch (\Throwable $e) {

            DB::rollBack();

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    private function handlePayment(array $requestData): bool
    {
        $dateString = $requestData['date'] ?? now()->format('Y-m-d');

        $year = date('Y', strtotime($dateString));
        $month = date('m', strtotime($dateString));

        $paymentData = [
            'user_id' => (int) $requestData['user_id'],
            'year' => $year,
            'month' => $month,
            'amount' => (float) $requestData['amount'],
            'type' => $requestData['type'],
            'note' => $requestData['note'] ?? null,
            'payment_method_id' => $requestData['payment_method_id'] ?? null,
            'paid_by' => Auth::id(),
        ];

        if ($paymentData['type'] == 'advanced_return' || $paymentData['type'] == 'due') {
            $this->accountTransactionService
                ->prepareForAccTransAndDetails($paymentData, 'credit', 'receipt');
        } elseif ($paymentData['type'] == 'advanced') {

            $this->accountTransactionService
                ->prepareForAccTransAndDetails($paymentData, 'debit', 'payment');
        }

        $this->createPayment($paymentData);

        return true;
    }
}
