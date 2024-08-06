<?php

namespace Modules\QuestionBank\Models;

use App\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\QuestionBank\Database\Factories\QuizFactory;

class Quiz extends Model
{
    use BelongsToTenant;
    use HasFactory, SoftDeletes;

    public const TABLE_NAME = 'quizzes';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'institute_id',
        'branch_id',
        'created_by',
        'priority',
        'title',
        'description',
        'guidelines',
        'show_description_on_course_page',
        'type',
        'question_ids',
        'start_time',
        'end_time',
        'has_time_limit',
        'time_limit_value',
        'time_limit_unit',
        'on_expiry',
        'marks_per_question',
        'negative_marks_per_wrong_answer',
        'pass_mark',
        'attempts_allowed',
        'enable_negative_marking',
        'result_visibility',
        'layout_pages',
        'shuffle_questions',
        'shuffle_options',
        'access_type',
        'access_password',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'question_ids' => 'array',
        'enable_negative_marking' => 'boolean',
        'show_description_on_course_page' => 'boolean',
        'has_time_limit' => 'boolean',
        'shuffle_questions' => 'boolean',
        'shuffle_options' => 'boolean',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'time_limit_value' => 'integer',
        'marks_per_question' => 'float',
        'negative_marks_per_wrong_answer' => 'float',
        'pass_mark' => 'float',
        'attempts_allowed' => 'integer',
    ];

    /** Seconds a single attempt may run, or null when the quiz is untimed. */
    public function timeLimitSeconds(): ?int
    {
        if (! $this->has_time_limit || ! $this->time_limit_value) {
            return null;
        }

        $perUnit = match ($this->time_limit_unit) {
            'seconds' => 1,
            'hours' => 3600,
            'days' => 86400,
            'weeks' => 604800,
            default => 60,
        };

        return (int) $this->time_limit_value * $perUnit;
    }

    /**
     * Whether a submission for an attempt started at $startedAt must be refused.
     * `prevent_submit` closes exactly at the limit; `auto_submit` / `grace_time`
     * allow a short grace for the client's own auto-submit to arrive. The quiz
     * end_time always closes submissions (with the same grace).
     */
    public function submissionWindowClosed($startedAt): bool
    {
        $grace = $this->on_expiry === 'prevent_submit' ? 0 : 60;
        $now = now();

        if ($this->end_time && $now->gt(Carbon::parse($this->end_time)->addSeconds($grace))) {
            return true;
        }

        $limit = $this->timeLimitSeconds();
        if ($limit === null || ! $startedAt) {
            return false;
        }

        return $now->gt(Carbon::parse($startedAt)->addSeconds($limit + $grace));
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class, 'id', 'question_ids');
    }

    public function attemptsAllowed()
    {
        return $this->hasMany(QuizAttempt::class, 'id', 'attempt_id');
    }

    protected static function newFactory(): QuizFactory
    {
        return QuizFactory::new();
    }
}
