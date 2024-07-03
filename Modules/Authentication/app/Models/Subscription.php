<?php

namespace Modules\Authentication\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['institute_id', 'plan_id', 'start_date', 'end_date', 'status', 'invoice_details'];

    protected $casts = [
        'invoice_details' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class)->select(['id', 'name']);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class)->select(['id', 'name']);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SubscriptionItem::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && now()->lte($this->end_date);
    }
}
