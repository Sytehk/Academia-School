<?php

namespace Modules\Academic\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WhatsAppTemplate extends Model
{
    use BelongsToTenant;
    use SoftDeletes;

    protected $fillable = [
        'institute_id',
        'branch_id',
        'name',
        'event',
        'message',
        'status',
    ];

    public const TABLE_NAME = 'whats_app_templates';

    protected $table = self::TABLE_NAME;
}
