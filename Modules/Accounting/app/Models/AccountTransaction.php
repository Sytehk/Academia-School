<?php

namespace Modules\Accounting\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Authentication\Models\User;

class AccountTransaction extends Model
{
    use BelongsToTenant;

    protected $fillable = ['institute_id', 'branch_id', 'category_id', 'voucher_id', 'fund_id', 'transaction_date', 'type', 'reference', 'description', 'created_by'];

    public function transactionDetails(): HasMany
    {
        return $this->hasMany(AccountTransactionDetail::class, 'account_transactions_id');
    }

    public function accountingCategory(): BelongsTo
    {
        return $this->belongsTo(AccountingCategory::class, 'category_id');
    }

    public function accountingFund(): BelongsTo
    {
        return $this->belongsTo(AccountingFund::class, 'fund_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
