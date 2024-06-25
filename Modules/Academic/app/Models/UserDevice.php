<?php

namespace Modules\Academic\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDevice extends Model
{
    // The real table is student_devices (student_id FK) — user_devices never
    // existed in any migration, so every query against this model 500'd with
    // "Base table or view not found." Relation name kept as user() since
    // EnrollmentController::studentDeviceDelete() already chains ->user?->user
    // (Student::user() resolves the actual login account) — fixing the target
    // here needed no controller change.
    protected $table = 'student_devices';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['student_id', 'device_name', 'mac_id', 'ip_address', 'device_info'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
