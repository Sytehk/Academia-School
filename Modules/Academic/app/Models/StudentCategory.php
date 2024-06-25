<?php

namespace Modules\Academic\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class StudentCategory extends Model
{
    use BelongsToTenant;

    protected $fillable = ['institute_id', 'branch_id', 'name'];
}
