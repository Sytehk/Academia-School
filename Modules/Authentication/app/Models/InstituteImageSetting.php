<?php

namespace Modules\Authentication\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InstituteImageSetting extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'institute_id',
        'header_logo_light_theme',
        'header_logo_dark_theme',
        'footer_logo_light_theme',
        'footer_logo_dark_theme',
        'favicon',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public const TABLE_NAME = 'institute_image_settings';

    protected $table = self::TABLE_NAME;
}
