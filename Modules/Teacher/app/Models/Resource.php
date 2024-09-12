<?php

namespace Modules\Teacher\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    use BelongsToTenant;

    protected $guarded = ['id'];
}
