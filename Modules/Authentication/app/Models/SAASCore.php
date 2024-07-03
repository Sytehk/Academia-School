<?php

namespace Modules\Authentication\Models;

use Illuminate\Database\Eloquent\Model;

class SAASCore extends Model
{
    protected $fillable = [
        'title',
        'description',
        'icon',
        'status',
    ];

    public const TABLE_NAME = 's_a_a_s_cores';

    protected $table = self::TABLE_NAME;
}
