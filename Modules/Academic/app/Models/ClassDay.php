<?php

namespace Modules\Academic\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class ClassDay extends Model
{
    use BelongsToTenant;

    protected $fillable = ['institute_id', 'branch_id', 'day', 'is_active'];

    protected $table = 'class_days';
}
