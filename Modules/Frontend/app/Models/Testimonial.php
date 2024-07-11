<?php

namespace Modules\Frontend\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Testimonial extends Model
{
    use BelongsToTenant;
    use HasFactory, SoftDeletes;

    protected $table = 'testimonials';

    public const TABLE_NAME = 'testimonials';

    protected $fillable = [
        'institute_id',
        'branch_id',
        'name',
        'description',
        'thumbnail_image',
        'video_url',
        'note',
        'ratting',
        'status',
    ];
}
