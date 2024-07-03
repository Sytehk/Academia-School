<?php

namespace Modules\Authentication\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Currency extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'symbol',
        'code',
        'country_code',
        'status',
    ];

    public const TABLE_NAME = 'currencies';

    protected $table = self::TABLE_NAME;
}
