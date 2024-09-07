<?php

namespace Modules\Transport\Services;

use App\Enums\UserLogAction;
use App\Enums\VoucherType;
use App\Traits\AccountingCalculationTrait;
use App\Traits\Trackable;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Models\AccountingFund;
use Modules\Accounting\Models\AccountingLedger;
use Modules\Transport\Models\TransportCollection;
use Modules\Transport\Models\TransportFeeConfig;
use Modules\Transport\Models\TransportMember;

class TransportCollectionService
{
    use AccountingCalculationTrait, Trackable;

    public function generateInvoiceNo(): string
    {
        $prefix = 'TRP'.now()->format('Ym');
        $last = TransportCollection::where('invoice_id', 'like', $prefix.'%')
            ->withTrashed()
            ->orderByDesc('invoice_id')
            ->value('invoice_id');

        $sequence = $last ? ((int) substr($last, strlen($prefix)) + 1) : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    public function getFeeConfig(int $routeId, ?int $stopId, int $sessionId): ?TransportFeeConfig
    {
        return TransportFeeConfig::where('session_id', $sessionId)
            ->where('route_id', $routeId)
            ->where(function ($query) use ($stopId) {
                $query->where('stop_id', $stopId)->orWhereNull('stop_id');
            })
            ->orderByRaw('stop_id IS NULL')
            ->first();
    }

    public function createCollection(array $data): TransportCollection
    {
        try {
            DB::beginTransaction();

            $transportMember = TransportMember::findOrFail($data['transport_member_id']);

            $feeAmount = (float) $data['fee_amount'];
            $discount = (float) ($data['discount'] ?? 0);
            $fine = (float) ($data['fine'] ?? 0);
            $totalPayable = $feeAmount - $discount + $fine;
            $totalPaid = (float) ($data['total_paid'] ?? 0);
            $totalDue = max($totalPayable - $totalPaid, 0);

            $collection = TransportCollection::create([
                'institute_id' => get_institute_id(),
                'branch_id' => get_branch_id(),
                'student_id' => $transportMember->student_id,
                'transport_member_id' => $transportMember->id,
                'invoice_id' => $this->generateInvoiceNo(),
                'invoice_date' => $data['invoice_date'] ?? now()->toDateString(),
                'session_id' => $data['session_id'],
                'month' => $data['month'] ?? now()->format('Y-m'),
                'fee_amount' => $feeAmount,
                'discount' => $discount,
                'fine' => $fine,
                'total_payable' => $totalPayable,
                'total_paid' => $totalPaid,
                'total_due' => $totalDue,
                'note' => $data['note'] ?? null,
                'ledger_id' => $data['ledger_id'] ?? null,
                'receive_ledger_id' => $data['receive_ledger_id'] ?? null,
                'fund_id' => $data['fund_id'] ?? null,
                'created_by' => getUserId(),
            ]);

            if ($totalPaid > 0) {
                $this->syncAccountingTransaction($collection);
            }

            DB::commit();

            return $collection;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw new StudentCollectionException($th->getMessage());
        }
    }

    public function syncAccountingTransaction(TransportCollection $collection): void
    {
        if (empty($collection->receive_ledger_id)) {
            return;
        }

        $accountingCategory = AccountingLedger::find($collection->ledger_id);

        $this->createAccountingTransaction([
            'institute_id' => get_institute_id(),
            'branch_id' => get_branch_id(),
            'category_id' => $accountingCategory->accounting_category_id ?? 1,
            'type' => VoucherType::RECEIPT,
            'transaction_date' => $collection->invoice_date,
            'voucher_id' => $collection->invoice_id,
            'fund_id' => $collection->fund_id,
            'fund_to_id' => null,
            'payment_method_id' => $collection->receive_ledger_id,
            'payment_method_to_id' => null,
            'reference' => null,
            'description' => 'Transport Fee Collection',
            'details' => [
                [
                    'fund_id' => $collection->fund_id,
                    'fund_to_id' => null,
                    'ledger_id' => $collection->receive_ledger_id,
                    'transaction_date' => $collection->invoice_date,
                    'payment_method_id' => $collection->receive_ledger_id,
                    'payment_method_to_id' => null,
                    'debit' => 0,
                    'credit' => $collection->total_paid,
                ],
            ],
        ]);

        $ledger = AccountingLedger::find($collection->receive_ledger_id)
            ?? AccountingLedger::query()
                ->where('ledger_name', 'Cash')
                ->where('institute_id', get_institute_id())
                ->where('branch_id', get_branch_id())
                ->first();
        $ledger?->increment('balance', $collection->total_paid);

        $fund = AccountingFund::find($collection->fund_id)
            ?? AccountingFund::query()
                ->where('name', 'General Fund')
                ->where('institute_id', get_institute_id())
                ->where('branch_id', get_branch_id())
                ->first();
        $fund?->increment('balance', $collection->total_paid);

        $this->trackAction(
            UserLogAction::COLLECT,
            TransportCollection::class,
            $collection->id,
            "Collected transport fee of {$collection->total_paid} for invoice {$collection->invoice_id}."
        );
    }
}
