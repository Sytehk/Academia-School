<?php

namespace Modules\Academic\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentAttendance extends Model
{
    use BelongsToTenant;

    protected $fillable = ['institute_id', 'branch_id', 'student_id', 'class_id', 'section_id', 'period_id', 'subject_id', 'date', 'attendance', 'check_in_at', 'check_out_at'];

    protected $casts = [
        'check_in_at' => 'datetime',
        'check_out_at' => 'datetime',
    ];

    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'class_id', 'id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class, 'period_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }
}
