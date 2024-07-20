<?php

namespace Modules\Examination\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class ShortCode extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'institute_id',
        'branch_id',
        'short_code_title',
        'short_code_note',
        'default_id',
        'total_mark',
        'accept_percent',
        'pass_mark',
    ];

    protected $casts = [
        'total_mark' => 'decimal:2',
        'accept_percent' => 'decimal:2',
        'pass_mark' => 'decimal:2',
    ];
}
