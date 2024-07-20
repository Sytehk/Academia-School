<?php

namespace Modules\Examination\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class MeritProcessType extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'institute_id',
        'branch_id',
        'type',
        'serial',
        'session_id',
    ];
}
