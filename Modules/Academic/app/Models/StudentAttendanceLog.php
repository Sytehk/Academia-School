<?php

namespace Modules\Academic\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single biometric (ZKTeco / device) punch: one student, one exact time, IN or OUT.
 *
 * BelongsToTenant only scopes queries when a user is authenticated; the device sync
 * endpoint is public, so institute_id/branch_id are always set explicitly on create.
 */
class StudentAttendanceLog extends Model
{
    use BelongsToTenant;

    public const TABLE_NAME = 'student_attendance_logs';

    public const TYPE_IN = 'IN';

    public const TYPE_OUT = 'OUT';

    protected $table = self::TABLE_NAME;

    protected $fillable = [
        'institute_id',
        'branch_id',
        'student_id',
        'class_id',
        'section_id',
        'attendance_date',
        'attendance_time',
        'type',
        'qr_code',
    ];

    protected $casts = [
        'attendance_date' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
