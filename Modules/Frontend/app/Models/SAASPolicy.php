<?php

namespace Modules\Frontend\Models;

use Illuminate\Database\Eloquent\Model;

class SAASPolicy extends Model
{
    protected $fillable = [
        'type',
        'description',
        'status',
        'created_by',
        'created_at',
        'updated_at',
    ];

    public const TABLE_NAME = 's_a_a_s_policies';

    protected $table = self::TABLE_NAME;
}
