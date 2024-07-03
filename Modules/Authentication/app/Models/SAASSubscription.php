<?php

namespace Modules\Authentication\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SAASSubscription extends Model
{
    use SoftDeletes;

    protected $table = 's_a_a_s_subscriptions';

    public const TABLE_NAME = 's_a_a_s_subscriptions';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'email',
        'created_at',
        'updated_at',
    ];
}
