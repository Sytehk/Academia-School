<?php

namespace Modules\Academic\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Period extends Model
{
    use BelongsToTenant;
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'institute_id',
        'branch_id',
        'period',
        'serial_no',
    ];
}
