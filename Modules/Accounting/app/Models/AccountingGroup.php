<?php

namespace Modules\Accounting\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountingGroup extends Model
{
    use BelongsToTenant;

    protected $fillable = ['institute_id', 'branch_id', 'accounting_category_id', 'name', 'code'];

    public function accountingCategory(): BelongsTo
    {
        return $this->belongsTo(AccountingCategory::class)->select('id', 'name');
    }

    public function accountingLedgers(): HasMany
    {
        return $this->hasMany(AccountingLedger::class, 'accounting_group_id', 'id');
    }
}
