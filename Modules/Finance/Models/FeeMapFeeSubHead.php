<?php

namespace Modules\Finance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FeeMapFeeSubHead extends Model
{
    use HasFactory;

    protected $table = 'fee_map_fee_sub_head';

    protected $guarded = ['id'];

    public function feeMaps(): BelongsToMany
    {
        return $this->belongsToMany(FeeMap::class, 'fee_map_fee_sub_head', 'fee_sub_head_id', 'fee_map_id');
    }
}
