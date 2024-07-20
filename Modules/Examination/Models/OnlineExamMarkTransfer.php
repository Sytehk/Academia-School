<?php

namespace Modules\Examination\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Academic\Models\Student;
use Modules\Authentication\Models\User;

class OnlineExamMarkTransfer extends Model
{
    use BelongsToTenant;

    public const ACTION_CREATED = 'created';

    public const ACTION_UPDATED = 'updated';

    public const ACTION_SKIPPED = 'skipped';

    protected $fillable = [
        'institute_id',
        'branch_id',
        'online_exam_id',
        'student_id',
        'attempt_id',
        'exam_mark_id',
        'online_score',
        'online_full_marks',
        'transferred_marks',
        'academic_full_marks',
        'converted',
        'action',
        'transferred_by',
        'transferred_at',
    ];

    protected $casts = [
        'online_score' => 'decimal:2',
        'online_full_marks' => 'decimal:2',
        'transferred_marks' => 'decimal:2',
        'academic_full_marks' => 'decimal:2',
        'converted' => 'boolean',
        'transferred_at' => 'datetime',
    ];

    public function onlineExam(): BelongsTo
    {
        return $this->belongsTo(OnlineExam::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(OnlineExamAttempt::class, 'attempt_id');
    }

    public function examMark(): BelongsTo
    {
        return $this->belongsTo(ExamMark::class);
    }

    public function transferredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transferred_by');
    }
}
