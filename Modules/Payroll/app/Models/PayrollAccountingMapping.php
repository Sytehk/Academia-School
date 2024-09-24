<?php

namespace Modules\Payroll\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Accounting\Models\AccountingFund;
use Modules\Accounting\Models\AccountingLedger;

class PayrollAccountingMapping extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = ['institute_id', 'branch_id', 'ledger_id', 'fund_id'];

    public function ledger(): BelongsTo
    {
        return $this->belongsTo(AccountingLedger::class, 'ledger_id', 'id')->select('id', 'ledger_name');
    }

    public function fund(): BelongsTo
    {
        return $this->belongsTo(AccountingFund::class, 'fund_id', 'id')->select('id', 'name');
    }

    /**
     * Resolve the mapping for the acting institute/branch. Falls back to an
     * institute_id-less row (or the first row) so already-installed single-tenant systems
     * that predate the institute_id/branch_id columns keep working after this update —
     * see the 2026_07_08 migration that added those columns.
     */
    public static function resolveForCurrentTenant(): ?self
    {
        $instituteId = get_institute_id();
        $branchId = get_branch_id();

        return static::where('institute_id', $instituteId)
            ->where('branch_id', $branchId)
            ->first()
            ?? static::whereNull('institute_id')->first()
            ?? static::first();
    }
}
