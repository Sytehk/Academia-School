<?php

namespace Modules\Examination\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Academic\Models\ClassModel;
use Modules\Academic\Models\Exam;
use Modules\Academic\Models\Section;
use Modules\Academic\Models\StudentGroup;
use Modules\Academic\Models\Subject;
use Modules\QuestionBank\Models\Quiz;

class OnlineExam extends Model
{
    use BelongsToTenant;

    public const POLICY_SKIP = 'skip';

    public const POLICY_UPDATE = 'update';

    public const POLICY_ASK = 'ask';

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_CLOSED = 'closed';

    /** Which submitted attempt counts when retakes are allowed. */
    public const SCORE_LATEST = 'latest';

    public const SCORE_BEST = 'best';

    public const SCORE_FIRST = 'first';

    public const RESULT_IMMEDIATE = 'immediate';

    public const RESULT_AFTER_END = 'after_end';

    public const RESULT_HIDDEN = 'hidden';

    protected $fillable = [
        'institute_id',
        'branch_id',
        'session_id',
        'exam_id',
        'class_id',
        'section_id',
        'group_id',
        'subject_id',
        'quiz_id',
        'name',
        'status',
        'published_at',
        'instructions',
        'duration_minutes',
        'start_at',
        'end_at',
        'total_questions',
        'questions_per_attempt',
        'total_marks',
        'pass_marks',
        'attempt_limit',
        'score_policy',
        'result_visibility',
        'negative_marking',
        'negative_marks_per_wrong',
        'random_questions',
        'random_options',
        'conversion_enabled',
        'online_full_marks',
        'academic_full_marks',
        'transfer_policy',
        'mark_component_slot',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'published_at' => 'datetime',
        'end_at' => 'datetime',
        'total_marks' => 'decimal:2',
        'pass_marks' => 'decimal:2',
        'negative_marking' => 'boolean',
        'negative_marks_per_wrong' => 'decimal:2',
        'random_questions' => 'boolean',
        'random_options' => 'boolean',
        'conversion_enabled' => 'boolean',
        'online_full_marks' => 'decimal:2',
        'academic_full_marks' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(StudentGroup::class, 'group_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(OnlineExamQuestion::class)->orderBy('display_order');
    }

    public function pools(): HasMany
    {
        return $this->hasMany(OnlineExamQuestionPool::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(OnlineExamAttempt::class);
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(OnlineExamMarkTransfer::class);
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED && $this->is_active;
    }

    /** Past its end time, or explicitly closed by staff. */
    public function hasEnded(): bool
    {
        return $this->status === self::STATUS_CLOSED || ($this->end_at && now()->gt($this->end_at));
    }

    /** Students may start (or resume) an attempt right now. */
    public function isOpen(): bool
    {
        if (! $this->isPublished() || $this->hasEnded()) {
            return false;
        }

        return ! ($this->start_at && now()->lt($this->start_at));
    }

    /** Whether a student may see their score for a submitted attempt. */
    public function resultVisible(): bool
    {
        return match ($this->result_visibility) {
            self::RESULT_HIDDEN => false,
            self::RESULT_AFTER_END => $this->hasEnded(),
            default => true,
        };
    }

    /** Correct answers are only revealed once nobody can still sit the exam. */
    public function answersRevealable(): bool
    {
        return $this->resultVisible() && $this->hasEnded();
    }
}
