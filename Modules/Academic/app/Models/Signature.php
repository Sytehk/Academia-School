<?php

namespace Modules\Academic\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Signature extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $fillable = ['institute_id', 'branch_id', 'user_id', 'place_at', 'title', 'image'];
}
