<?php

namespace Modules\Finance\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeDateConfig extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = ['institute_id', 'branch_id', 'fee_sub_head_id', 'payable_date_start', 'payable_date_end'];
}
