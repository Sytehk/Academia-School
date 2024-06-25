<?php

namespace Modules\Academic\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Authentication\Models\User;

class SmsLog extends Model
{
    use BelongsToTenant;
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $table = 'sms_logs';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
