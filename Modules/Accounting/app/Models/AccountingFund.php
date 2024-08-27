<?php

namespace Modules\Accounting\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountingFund extends Model
{
    use BelongsToTenant;

    protected $fillable = ['institute_id', 'branch_id', 'serial', 'name', 'cash_in', 'cash_out', 'balance'];

    public function accountingFundsTransactions(): HasMany
    {
        return $this->hasMany(AccountTransactionDetail::class, 'fund_id');
    }

    // Common scope for filtering by institute & branch
    public function scopeInstituteBranch($query)
    {
        return $query->where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id());
    }
}
