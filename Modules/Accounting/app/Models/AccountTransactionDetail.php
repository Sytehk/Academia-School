<?php

namespace Modules\Accounting\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Payroll\Models\PaymentMethod;

class AccountTransactionDetail extends Model
{
    use BelongsToTenant;

    protected $fillable = ['institute_id', 'branch_id', 'account_transactions_id', 'ledger_id', 'debit', 'credit', 'fund_id', 'fund_to_id', 'transaction_date'];

    public function accountTransaction(): BelongsTo
    {
        return $this->belongsTo(AccountTransaction::class, 'id');
    }

    public function accountingLedger(): BelongsTo
    {
        return $this->belongsTo(AccountingLedger::class, 'ledger_id');
    }

    public function transaction()
    {
        return $this->belongsTo(AccountTransaction::class, 'account_transactions_id');
    }

    public function fund()
    {
        return $this->belongsTo(AccountingFund::class, 'fund_id');
    }

    public function fundTo()
    {
        return $this->belongsTo(AccountingFund::class, 'fund_to_id');
    }

    public function ledger()
    {
        return $this->belongsTo(AccountingLedger::class, 'ledger_id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    public function paymentMethodTo()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_to_id');
    }
}
