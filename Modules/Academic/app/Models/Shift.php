<?php

namespace Modules\Academic\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shift extends Model
{
    use BelongsToTenant;
    use HasFactory, SoftDeletes;

    protected $table = 'shifts';

    protected $guarded = ['id'];
}
