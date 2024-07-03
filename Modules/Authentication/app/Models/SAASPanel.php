<?php

namespace Modules\Authentication\Models;

use Illuminate\Database\Eloquent\Model;

class SAASPanel extends Model
{
    protected $fillable = [
        'title',
        'description',
        'icon',
        'button_name',
        'button_link',
        'status',
    ];

    public const TABLE_NAME = 's_a_a_s_panels';

    protected $table = self::TABLE_NAME;
}
