<?php

namespace Modules\Frontend\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReadyToJoinUs extends Model
{
    use BelongsToTenant;
    use SoftDeletes;

    protected $table = 'ready_to_join_us';

    public const TABLE_NAME = 'ready_to_join_us';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'institute_id',
        'branch_id',
        'title',
        'description',
        'icon',
        'button_name',
        'button_link',
    ];
}
