<?php

namespace Modules\Frontend\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Hash;

/**
 * A public sign-up request.
 *
 * `collected_data` holds the submitted form payload. It must never contain a
 * plaintext password: the record is retained indefinitely for audit, so anything
 * stored here is stored forever. Use {@see collectedDataFrom()} to build the
 * payload, which hashes the password on the way in.
 */
class Onboarding extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    /**
     * Timestamps are managed by Eloquent and must not be mass assignable — a
     * caller could otherwise forge created_at/approved_at from request input.
     */
    protected $fillable = [
        'collected_data',
        'institute_logo',
        'user_avatar',
        'status',
    ];

    protected $casts = [
        'collected_data' => 'array',
        'approved_at' => 'datetime',
    ];

    /**
     * Requests still awaiting provisioning.
     *
     * Filters on both columns deliberately. approved_at is the column the
     * provisioning flow stamps, while status is what the UI displays; checking
     * only one of them is how completed sign-ups leaked back into the pending
     * list.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->whereNull('approved_at')
            ->where('status', self::STATUS_PENDING);
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED || $this->approved_at !== null;
    }

    /**
     * Build the `collected_data` payload, hashing the password.
     *
     * The hash is stored rather than the plaintext, and is later assigned to the
     * user as-is: the User model's `hashed` cast passes an existing hash through
     * untouched, so it must not be re-hashed at provisioning time.
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    public static function collectedDataFrom(array $validated): array
    {
        $payload = collect($validated)
            ->only([
                'institute_name',
                'institute_email',
                'institute_phone',
                'institute_domain',
                'institute_type',
                'institute_address',
                'user_name',
                'user_email',
                'user_phone',
            ])
            ->all();

        $payload['password'] = Hash::make($validated['password']);

        return $payload;
    }
}
