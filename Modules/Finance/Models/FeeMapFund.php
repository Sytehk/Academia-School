<?php

namespace Modules\Finance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FeeMapFund extends Model
{
    use HasFactory;

    protected $table = 'fee_map_fund';

    protected $guarded = ['id'];

    public function feeMaps(): BelongsToMany
    {
        return $this->belongsToMany(FeeMap::class, 'fee_map_fund', 'fund_id', 'fee_map_id');
    }
}
