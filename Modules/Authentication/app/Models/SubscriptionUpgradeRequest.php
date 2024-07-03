<?php

namespace Modules\Authentication\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionUpgradeRequest extends Model
{
    protected $fillable = [
        'institute_id',
        'plan_id',
        'extra_days',
        'amount_paid',
        'payment_method',
        'notes',
        'status',
        'approved_by',
        'approved_at',
    ];

    public function institute()
    {
        return $this->belongsTo(Institute::class)->select('id', 'name');
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class)->select('id', 'name', 'price', 'duration_days');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
