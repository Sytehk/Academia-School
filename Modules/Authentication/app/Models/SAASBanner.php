<?php

namespace Modules\Authentication\Models;

use Illuminate\Database\Eloquent\Model;

class SAASBanner extends Model
{
    protected $fillable = [
        'title',
        'description',
        'button_name',
        'button_link',
        'button_two_name',
        'button_two_link',
        'image',
        'status',
        'created_by',
    ];

    protected $casts = [
        'status' => 'integer',
        'created_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public const TABLE_NAME = 's_a_a_s_banners';

    protected $table = self::TABLE_NAME;
}
