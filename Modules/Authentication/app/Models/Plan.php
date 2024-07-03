<?php

namespace Modules\Authentication\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use SoftDeletes;

    public const TABLE_NAME = 'plans';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['name', 'description', 'student_limit', 'branch_limit', 'price', 'duration_days', 'is_custom', 'is_free', 'status'];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
