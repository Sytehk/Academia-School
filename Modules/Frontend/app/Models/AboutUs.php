<?php

namespace Modules\Frontend\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AboutUs extends Model
{
    use BelongsToTenant;
    use HasFactory, SoftDeletes;

    protected $table = 'about_us';

    public const TABLE_NAME = 'about_us';

    protected $fillable = [
        'institute_id',
        'branch_id',
        'title',
        'description',
        'long_description',
        'image',
        'status',
        'created_by',
        'created_at',
        'updated_at',
    ];
}
