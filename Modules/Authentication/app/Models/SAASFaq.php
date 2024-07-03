<?php

namespace Modules\Authentication\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SAASFaq extends Model
{
    use SoftDeletes;

    protected $table = 's_a_a_s_faqs';

    public const TABLE_NAME = 's_a_a_s_faqs';

    protected $fillable = [
        'question',
        'answer',
        'status',
        'created_by',
        'created_at',
        'updated_at',
    ];
}
