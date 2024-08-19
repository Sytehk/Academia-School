<?php

namespace Modules\Finance\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentWaiverConfig extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $guarded = ['id'];
}
