<?php

namespace Modules\Frontend\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WhyChooseUs extends Model
{
    use BelongsToTenant;
    use SoftDeletes;

    protected $table = 'why_choose_us';

    public const TABLE_NAME = 'why_choose_us';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'institute_id',
        'branch_id',
        'title',
        'description',
        'icon',
    ];
}
