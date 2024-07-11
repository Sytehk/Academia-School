<?php

namespace Modules\Frontend\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Banner extends Model
{
    use BelongsToTenant;
    use HasFactory, SoftDeletes;

    protected $table = 'banners';

    public const TABLE_NAME = 'banners';

    protected $fillable = [
        'institute_id',
        'title',
        'description',
        'button_name',
        'button_link',
        'image',
        'status',
        'created_by',
        'created_at',
        'updated_at',
    ];
}
