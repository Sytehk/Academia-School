<?php

namespace Modules\Authentication\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Academic\Models\Student;
use Modules\Frontend\Models\Theme;

class Institute extends Model
{
    use SoftDeletes;

    public const TABLE_NAME = 'institutes';

    protected $fillable = [
        'institute_id',
        'branch_id',
        'owner_id',
        'assigned_to',
        'theme_id',
        'name',
        'email',
        'address',
        'institute_type',
        'phone',
        'domain',
        'platform',
        'logo',
        'status',
        'admission_no_mode',
        'admission_no_prefix',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Attribute Casting
     */
    protected $casts = [
        'owner_id' => 'integer',
        'assigned_to' => 'integer',
        'status' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the owner of the institute.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the user assigned to this institute.
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Relationship: Institute has many Students
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    /**
     * Relationship: Institute has many Branches
     */
    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    /**
     * Relationship: Institute has many Subscriptions
     */
    public function subscription()
    {
        return $this->hasOne(Subscription::class)->select('id', 'institute_id', 'plan_id', 'start_date', 'end_date', 'status')->with('plan');
    }

    /**
     * Relationship: Institute has many Subscriptions
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get the currently active subscription
     */
    public function activeSubscription()
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->whereDate('end_date', '>=', now())
            ->latest('start_date')
            ->first();
    }

    public function theme()
    {
        return $this->belongsTo(Theme::class);
    }
}
