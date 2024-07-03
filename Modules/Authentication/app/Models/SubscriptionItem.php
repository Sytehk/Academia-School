<?php

namespace Modules\Authentication\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionItem extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['subscription_id', 'reference_no', 'amount_paid', 'payment_method'];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
