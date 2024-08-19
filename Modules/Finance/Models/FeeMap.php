<?php

namespace Modules\Finance\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Accounting\Models\AccountingFund;
use Modules\Accounting\Models\AccountingLedger;

class FeeMap extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = ['institute_id', 'branch_id', 'fee_head_id', 'ledger_id', 'fund', 'type'];

    public function feeHead(): BelongsTo
    {
        return $this->belongsTo(FeeHead::class, 'fee_head_id');
    }

    public function feeLedger(): BelongsTo
    {
        return $this->belongsTo(AccountingLedger::class, 'ledger_id');
    }

    public function feeSubHeads(): BelongsToMany
    {
        return $this->belongsToMany(FeeSubHead::class, 'fee_map_fee_sub_head', 'fee_map_id', 'fee_sub_head_id');
    }

    public function funds(): BelongsToMany
    {
        // fee_map_fund is the pivot table linking to accounting_funds — not to the
        // FeeMapFund model, which is itself just an Eloquent wrapper around that same
        // pivot table. Relating through FeeMapFund made this a self-join on one table
        // (fee_map_fund joined to fee_map_fund), which MySQL rejects outright the moment
        // the relation is read (funds() was previously only ever attach()'d/deleted via
        // raw SQL, never eager-loaded, so this never fired).
        return $this->belongsToMany(AccountingFund::class, 'fee_map_fund', 'fee_map_id', 'fund_id');
    }
}
