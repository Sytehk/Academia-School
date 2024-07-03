<?php

namespace Modules\Authentication\Models;

use Illuminate\Database\Eloquent\Model;

class InstituteType extends Model
{
    protected $fillable = [
        'title',
        'image',
        'status',
    ];

    public const TABLE_NAME = 'institute_types';

    protected $table = self::TABLE_NAME;
}
