<?php

namespace Modules\Authentication\Models;

use Illuminate\Database\Eloquent\Model;

class SAASSetting extends Model
{
    protected $table = 's_a_a_s_settings';

    /** Explicit allow-list — `$guarded = ['id']` permitted mass-assigning name/type/mode. */
    protected $fillable = [
        'name',
        'value',
        'payment_info',
        'sms_info',
        'type',
        'mode',
        'status',
    ];

    // cast value will be json decode
    protected $casts = [
        'payment_info' => 'array',
        'sms_info' => 'array',
    ];
}
