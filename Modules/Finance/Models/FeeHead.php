<?php

namespace Modules\Finance\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FeeHead extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = ['institute_id', 'branch_id', 'serial', 'name'];

    protected $with = ['feeSubHeads'];

    public function feeSubHead(): BelongsTo
    {
        return $this->belongsTo(FeeMapFeeSubHead::class, 'fee_sub_head_id');
    }

    public function feeSubHeads(): BelongsToMany
    {
        return $this->belongsToMany(
            FeeSubHead::class,
            'fee_map_fee_sub_head',
            'fee_head_id',
            'fee_sub_head_id'
        )
            ->orderBy('serial', 'asc');
    }
}
