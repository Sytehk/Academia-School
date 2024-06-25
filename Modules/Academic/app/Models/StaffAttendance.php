<?php

namespace Modules\Academic\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Authentication\Models\User;

class StaffAttendance extends Model
{
    use BelongsToTenant;

    protected $fillable = ['institute_id', 'branch_id', 'user_id', 'date', 'start_time', 'end_time', 'attendance', 'type'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
