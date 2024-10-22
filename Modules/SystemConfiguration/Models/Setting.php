<?php

namespace Modules\SystemConfiguration\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';

    /** Every writable column is listed explicitly — `$guarded = ['id']` let a
     *  request mass-assign institute_id/branch_id/name/type onto a settings row. */
    protected $fillable = [
        'institute_id',
        'branch_id',
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
