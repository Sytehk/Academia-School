<?php

namespace Modules\Academic\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WhatsAppSetting extends Model
{
    use BelongsToTenant;
    use SoftDeletes;

    protected $fillable = [
        'institute_id',
        'branch_id',
        'provider',
        'access_token',
        'phone_number_id',
        'business_account_id',
        'language',
        'status',
    ];

    public const TABLE_NAME = 'whats_app_settings';

    protected $table = self::TABLE_NAME;

    protected $casts = [
        'status' => 'boolean',
    ];
}
