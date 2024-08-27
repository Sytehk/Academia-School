<?php

namespace Modules\Accounting\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountingLedger extends Model
{
    use BelongsToTenant;

    protected $fillable = ['institute_id', 'branch_id', 'ledger_name', 'accounting_category_id', 'accounting_group_id', 'type', 'balance'];

    public function accountingCategory(): BelongsTo
    {
        return $this->belongsTo(AccountingCategory::class, 'accounting_category_id', 'id');
    }

    public function accountingGroup(): BelongsTo
    {
        return $this->belongsTo(AccountingGroup::class, 'accounting_group_id', 'id');
    }

    // Common scope for filtering by institute & branch
    public function scopeInstituteBranch($query)
    {
        return $query->where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id());
    }
}
