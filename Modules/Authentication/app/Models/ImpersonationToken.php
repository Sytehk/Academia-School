<?php

namespace Modules\Authentication\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImpersonationToken extends Model
{
    protected $fillable = [
        'token',
        'user_id',
        'institute_id',
        'initiated_by',
        'expires_at',
        'used_at',
        'used_ip',
        'return_url',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function isConsumable(): bool
    {
        return $this->used_at === null && $this->expires_at->isFuture();
    }
}
