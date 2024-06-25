<?php

namespace Modules\Academic\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Picklist extends Model
{
    use BelongsToTenant;
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $table = 'picklists';
}
