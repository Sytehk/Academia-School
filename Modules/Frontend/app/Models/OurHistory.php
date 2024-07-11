<?php

namespace Modules\Frontend\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OurHistory extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $table = 'our_histories';

    public const TABLE_NAME = 'our_histories';

    protected $fillable = [
        'institute_id',
        'branch_id',
        'year',
        'title',
        'descriptions',
        'status',
        'created_by',
        'created_at',
        'updated_at',
    ];
}
