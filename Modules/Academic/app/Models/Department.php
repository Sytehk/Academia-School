<?php

namespace Modules\Academic\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use BelongsToTenant;
    use SoftDeletes;

    protected $table = 'departments';

    protected $fillable = [
        'institute_id',
        'branch_id',
        'department_name',
        'slug',
        'priority',
    ];
}
