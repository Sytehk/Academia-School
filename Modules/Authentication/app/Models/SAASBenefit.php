<?php

namespace Modules\Authentication\Models;

use Illuminate\Database\Eloquent\Model;

class SAASBenefit extends Model
{
    protected $fillable = [
        'heading',
        'title',
        'description',
        'para_title_one',
        'para_title_two',
        'para_title_three',
        'para_title_four',
        'image',
        'status',
        'created_by',
    ];

    public const TABLE_NAME = 's_a_a_s_benefits';

    protected $table = self::TABLE_NAME;
}
