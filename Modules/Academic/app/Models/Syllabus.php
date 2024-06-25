<?php

namespace Modules\Academic\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Syllabus extends Model
{
    use BelongsToTenant;

    protected $table = 'syllabus';

    protected $guarded = ['id'];
}
