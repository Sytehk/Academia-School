<?php

namespace Modules\Frontend\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use BelongsToTenant;
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'institute_id',
        'branch_id',
        'title',
        'slug',
        'type',
        'content',
        'meta_data',
        'seo_meta_keywords',
        'seo_meta_description',
        'author_id',
    ];
}
