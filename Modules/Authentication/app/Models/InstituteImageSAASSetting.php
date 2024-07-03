<?php

namespace Modules\Authentication\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InstituteImageSAASSetting extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'header_logo_light_theme',
        'header_logo_dark_theme',
        'footer_logo_light_theme',
        'footer_logo_dark_theme',
        'banner_image',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public const TABLE_NAME = 'institute_image_s_a_a_s_settings';

    protected $table = self::TABLE_NAME;
}
