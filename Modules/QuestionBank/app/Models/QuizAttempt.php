<?php

namespace Modules\QuestionBank\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Authentication\Models\User;

class QuizAttempt extends Model
{
    use BelongsToTenant;
    use HasFactory, SoftDeletes;

    public const TABLE_NAME = 'quiz_attempts';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'institute_id',
        'branch_id',
        'quiz_id',
        'user_id',
        'answers',
        'score',
        'status',
        'started_at',
        'submitted_at',
        'time_taken',
        'deleted_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id')->select('id', 'name');
    }

    public function result(): HasOne
    {
        return $this->hasOne(QuizResult::class, 'quiz_attempt_id');
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }
}
