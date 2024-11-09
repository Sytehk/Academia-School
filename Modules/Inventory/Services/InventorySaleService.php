<?php

namespace Modules\Inventory\Services;

use App\Enums\VoucherType;
use App\Traits\AccountingCalculationTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Accounting\Models\AccountingLedger;
use Modules\Inventory\Exceptions\InventorySaleException;
use Modules\Inventory\Models\InventoryItem;
use Modules\Inventory\Models\InventorySale;

/**
 * Posts an inventory sale (paid or partly/fully due) the same way Finance
 * posts a tuition collection: a due amount is simply total_payable - total_paid,
 * and any amount actually paid now is pushed into the shared accounting ledger
 * via AccountingCalculationTrait — the same mechanism StudentCollectionTrait
 * uses. Stock is decremented atomically in the same transaction.
 */
class InventorySaleService
{
    use AccountingCalculationTrait;

    /**
     * @param  array{student_id:int, ledger_id:?int, receive_ledger_id:?int, fund_id:?int, note:?string, paid_now:float, items: array<int, array{item_id:int, quantity:int}>}  $data
     */
    public function createSale(array $data): InventorySale
    {
        return DB::transaction(function () use ($data) {
            $items = collect($data['items'])->map(function ($row) {
                $item = InventoryItem::findOrFail($row['item_id']);
                $quantity = (int) $row['quantity'];

                if ($quantity < 1) {
                    throw new InventorySaleException(_lang('Quantity must be at least 1.'));
                }

                if ($item->stock_quantity < $quantity) {
                    throw new InventorySaleException(_lang('Not enough stock for').' '.$item->name.'.');
                }

                return [
                    'item' => $item,
                    'quantity' => $quantity,
                    'unit_price' => (float) $item->selling_price,
                    'line_total' => round((float) $item->selling_price * $quantity, 2),
                ];
            });

            $totalPayable = round((float) $items->sum('line_total'), 2);
            $paidNow = min((float) ($data['paid_now'] ?? 0), $totalPayable);
            $totalDue = max(0, $totalPayable - $paidNow);

            $sale = InventorySale::create([
                'institute_id' => get_institute_id(),
                'branch_id' => get_branch_id(),
                'student_id' => $data['student_id'],
                'invoice_id' => $this->generateInvoiceId(),
                'sale_date' => now()->toDateString(),
                'total_payable' => $totalPayable,
                'total_paid' => $paidNow,
                'total_due' => $totalDue,
                'ledger_id' => $data['ledger_id'] ?? null,
                'receive_ledger_id' => $data['receive_ledger_id'] ?? null,
                'fund_id' => $data['fund_id'] ?? null,
                'note' => $data['note'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($items as $row) {
                $sale->items()->create([
                    'item_id' => $row['item']->id,
                    'quantity' => $row['quantity'],
                    'unit_price' => $row['unit_price'],
                    'line_total' => $row['line_total'],
                ]);

                $row['item']->decrement('stock_quantity', $row['quantity']);
            }

            if ($paidNow > 0) {
                $this->syncAccountingTransaction($sale);
            }

            return $sale->fresh('items.item');
        });
    }

    private function syncAccountingTransaction(InventorySale $sale): void
    {
        // No receive ledger configured — nothing to post, same rule Finance follows.
        if (empty($sale->receive_ledger_id)) {
            return;
        }

        $accountingLedger = AccountingLedger::find($sale->receive_ledger_id);

        $this->createAccountingTransaction([
            'category_id' => $accountingLedger->accounting_category_id ?? 1,
            'type' => VoucherType::RECEIPT,
            'transaction_date' => $sale->sale_date,
            'fund_id' => $sale->fund_id,
            'fund_to_id' => null,
            'payment_method_id' => $sale->receive_ledger_id,
            'payment_method_to_id' => null,
            'reference' => $sale->invoice_id,
            'description' => 'Inventory Sale',
            'details' => [
                [
                    'fund_id' => $sale->fund_id,
                    'fund_to_id' => null,
                    'ledger_id' => $sale->receive_ledger_id,
                    'transaction_date' => $sale->sale_date,
                    'payment_method_id' => $sale->receive_ledger_id,
                    'payment_method_to_id' => null,
                    'debit' => 0,
                    'credit' => $sale->total_paid,
                ],
            ],
        ]);

        if ($accountingLedger) {
            $accountingLedger->increment('balance', $sale->total_paid);
        }
    }

    private function generateInvoiceId(): string
    {
        do {
            $invoiceId = 'INV-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (InventorySale::withTrashed()->where('invoice_id', $invoiceId)->exists());

        return $invoiceId;
    }
}
