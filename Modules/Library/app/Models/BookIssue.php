<?php

namespace Modules\Library\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class BookIssue extends Model
{
    use BelongsToTenant;

    protected $guarded = ['id'];
}
