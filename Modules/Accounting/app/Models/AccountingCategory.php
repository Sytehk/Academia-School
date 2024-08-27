<?php

namespace Modules\Accounting\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountingCategory extends Model
{
    use BelongsToTenant;

    protected $fillable = ['institute_id', 'branch_id', 'name', 'code', 'type', 'nature'];

    public function accountingGroups(): HasMany
    {
        return $this->hasMany(AccountingGroup::class, 'accounting_category_id', 'id')->with('accountingLedgers');
    }
}
