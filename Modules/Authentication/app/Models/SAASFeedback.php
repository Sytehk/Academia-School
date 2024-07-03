<?php

namespace Modules\Authentication\Models;

use Illuminate\Database\Eloquent\Model;

class SAASFeedback extends Model
{
    protected $fillable = [
        'name',
        'description',
        'thumbnail_image',
        'video_url',
        'note',
        'ratting',
        'status',
    ];

    protected $casts = [
        'ratting' => 'integer',
        'status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public const TABLE_NAME = 's_a_a_s_feedback';

    protected $table = self::TABLE_NAME;
}
