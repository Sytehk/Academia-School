<?php

namespace Modules\Examination\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Academic\Models\Student;

class OnlineExamAttempt extends Model
{
    use BelongsToTenant;

    public const STATUS_STARTED = 'started';

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_AUTO_SUBMITTED = 'auto_submitted';

    /** No manually graded questions in this attempt. */
    public const GRADING_NONE = 'none';

    /** Descriptive / short answers still waiting for a teacher. */
    public const GRADING_PENDING = 'pending';

    public const GRADING_GRADED = 'graded';

    protected $fillable = [
        'institute_id',
        'branch_id',
        'online_exam_id',
        'student_id',
        'user_id',
        'attempt_no',
        'answers',
        'score',
        'max_score',
        'auto_score',
        'converted_score',
        'correct_count',
        'incorrect_count',
        'skipped_count',
        'status',
        'grading_status',
        'started_at',
        'expires_at',
        'submitted_at',
        'time_taken',
        'is_passed',
    ];

    protected $casts = [
        'answers' => 'array',
        'score' => 'decimal:2',
        'converted_score' => 'decimal:2',
        'max_score' => 'decimal:2',
        'auto_score' => 'decimal:2',
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'submitted_at' => 'datetime',
        'is_passed' => 'boolean',
    ];

    public function onlineExam(): BelongsTo
    {
        return $this->belongsTo(OnlineExam::class);
    }

    public function attemptQuestions(): HasMany
    {
        return $this->hasMany(OnlineExamAttemptQuestion::class, 'attempt_id')->orderBy('display_order');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && now()->gte($this->expires_at);
    }

    public function isSubmitted(): bool
    {
        return in_array($this->status, [self::STATUS_SUBMITTED, self::STATUS_AUTO_SUBMITTED], true);
    }

    /** Submitted and not waiting on manual grading — the score is final. */
    public function isFinal(): bool
    {
        return $this->isSubmitted() && $this->grading_status !== self::GRADING_PENDING;
    }
}
