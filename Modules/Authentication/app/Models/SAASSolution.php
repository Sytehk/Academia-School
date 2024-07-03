<?php

namespace Modules\Authentication\Models;

use Illuminate\Database\Eloquent\Model;

class SAASSolution extends Model
{
    protected $fillable = [
        'title',
        'description',
        'icon',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public const TABLE_NAME = 's_a_a_s_solutions';

    protected $table = self::TABLE_NAME;
}
