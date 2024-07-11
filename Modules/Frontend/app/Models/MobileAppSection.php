<?php

namespace Modules\Frontend\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MobileAppSection extends Model
{
    use BelongsToTenant;
    use SoftDeletes;

    protected $table = 'mobile_app_sections';

    public const TABLE_NAME = 'mobile_app_sections';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'institute_id',
        'branch_id',
        'title',
        'heading',
        'description',
        'image',
        'feature_one',
        'feature_two',
        'feature_three',
        'play_store_link',
        'app_store_link',
    ];
}
