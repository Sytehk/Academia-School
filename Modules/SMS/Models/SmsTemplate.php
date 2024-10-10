<?php

namespace Modules\SMS\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsTemplate extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $fillable = [
        'institute_id',
        'branch_id',
        'name',
        'description',
    ];
}
