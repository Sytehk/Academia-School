<?php

namespace Modules\Academic\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use BelongsToTenant;

    protected $guarded = ['id'];

    protected $table = 'events';
}
