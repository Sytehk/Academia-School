<?php

namespace Modules\Examination\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One question as dealt to one attempt: its position, the order its options
 * were shown in, the student's answer and the marks awarded (automatically, or
 * by a teacher for descriptive answers).
 */
class OnlineExamAttemptQuestion extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'institute_id',
        'branch_id',
        'attempt_id',
        'online_exam_question_id',
        'display_order',
        'option_order',
        'answer',
        'is_correct',
        'max_marks',
        'awarded_marks',
        'needs_grading',
        'graded_by',
        'graded_at',
        'feedback',
    ];

    protected $casts = [
        'option_order' => 'array',
        'answer' => 'array',
        'is_correct' => 'boolean',
        'max_marks' => 'decimal:2',
        'awarded_marks' => 'decimal:2',
        'needs_grading' => 'boolean',
        'graded_at' => 'datetime',
    ];

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(OnlineExamAttempt::class, 'attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(OnlineExamQuestion::class, 'online_exam_question_id');
    }
}
