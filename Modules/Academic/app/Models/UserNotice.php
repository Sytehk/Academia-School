<?php

namespace Modules\Academic\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class UserNotice extends Model
{
    use BelongsToTenant;

    protected $table = 'user_notices';

    protected $guarded = ['id'];
}
