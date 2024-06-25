<?php

namespace Modules\Academic\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveTypes extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $guarded = ['id'];
}
