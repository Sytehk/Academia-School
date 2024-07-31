<?php

namespace Modules\Hostel\Services;

use App\Enums\UserLogAction;
use App\Enums\VoucherType;
use App\Traits\AccountingCalculationTrait;
use App\Traits\Trackable;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Models\AccountingFund;
use Modules\Accounting\Models\AccountingLedger;
use Modules\Hostel\Models\HostelCollection;
use Modules\Hostel\Models\HostelCollectionDetail;
use Modules\Hostel\Models\HostelDepositRefund;
use Modules\Hostel\Models\HostelFeeConfig;
use Modules\Hostel\Models\RoomMember;

class HostelCollectionService
{
    use AccountingCalculationTrait, Trackable;

    public function generateInvoiceNo(): string
    {
        $prefix = 'HST'.now()->format('Ym');
        $last = HostelCollection::where('invoice_id', 'like', $prefix.'%')
            ->withTrashed()
            ->orderByDesc('invoice_id')
            ->value('invoice_id');

        $sequence = $last ? ((int) substr($last, strlen($prefix)) + 1) : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    public function getFeeConfig(int $hostelCategoryId, int $sessionId): ?HostelFeeConfig
    {
        return HostelFeeConfig::where('hostel_category_id', $hostelCategoryId)
            ->where('session_id', $sessionId)
            ->first();
    }

    /**
     * $data['fee_heads'] is an assoc array like ['admission' => 500, 'monthly' => 100, 'security_deposit' => 200]
     * only non-zero heads become HostelCollectionDetail rows.
     */
    public function createCollection(array $data): HostelCollection
    {
        try {
            DB::beginTransaction();

            $roomMember = RoomMember::findOrFail($data['room_member_id']);

            $feeHeads = array_filter($data['fee_heads'] ?? [], fn ($amount) => (float) $amount > 0);
            $feeTotal = array_sum($feeHeads);

            $discount = (float) ($data['discount'] ?? 0);
            $fine = (float) ($data['fine'] ?? 0);
            $totalPayable = $feeTotal - $discount + $fine;
            $totalPaid = (float) ($data['total_paid'] ?? 0);
            $totalDue = max($totalPayable - $totalPaid, 0);

            $collection = HostelCollection::create([
                'institute_id' => get_institute_id(),
                'branch_id' => get_branch_id(),
                'student_id' => $roomMember->student_id,
                'room_member_id' => $roomMember->id,
                'invoice_id' => $this->generateInvoiceNo(),
                'invoice_date' => $data['invoice_date'] ?? now()->toDateString(),
                'session_id' => $data['session_id'],
                'month' => $data['month'] ?? null,
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

            foreach ($feeHeads as $head => $amount) {
                HostelCollectionDetail::create([
                    'hostel_collection_id' => $collection->id,
                    'fee_head' => $head,
                    'amount' => $amount,
                    'is_refundable' => $head === 'security_deposit',
                ]);
            }

            if ($totalPaid > 0) {
                $this->syncAccountingTransaction($collection);
            }

            DB::commit();

            return $collection;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw new \Exception($th->getMessage());
        }
    }

    public function syncAccountingTransaction(HostelCollection $collection): void
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
            'fund_id' => $collection->fund_id,
            'fund_to_id' => null,
            'payment_method_id' => $collection->receive_ledger_id,
            'payment_method_to_id' => null,
            'reference' => $collection->invoice_id,
            'description' => 'Hostel Fee Collection',
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
            HostelCollection::class,
            $collection->id,
            "Collected hostel fee of {$collection->total_paid} for invoice {$collection->invoice_id}."
        );
    }

    public function refundDeposit(int $hostelCollectionDetailId, float $amount, ?string $reason = null): void
    {
        $detail = HostelCollectionDetail::with('collection')->findOrFail($hostelCollectionDetailId);

        HostelDepositRefund::create([
            'institute_id' => get_institute_id(),
            'branch_id' => get_branch_id(),
            'hostel_collection_detail_id' => $detail->id,
            'student_id' => $detail->collection->student_id,
            'refund_amount' => $amount,
            'refund_date' => now()->toDateString(),
            'reason' => $reason,
            'processed_by' => getUserId(),
        ]);
    }
}
