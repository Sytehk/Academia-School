<?php

namespace Modules\Finance\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeSubHead extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = ['institute_id', 'branch_id', 'serial', 'name'];

    public function dateConfig(): BelongsTo
    {
        return $this->belongsTo(FeeDateConfig::class, 'fee_sub_head_id', 'id');
    }
}
