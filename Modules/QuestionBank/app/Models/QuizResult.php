<?php

namespace Modules\QuestionBank\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuizResult extends Model
{
    use BelongsToTenant;
    use HasFactory, SoftDeletes;

    public const TABLE_NAME = 'quiz_results';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'institute_id',
        'quiz_id',
        'quiz_attempt_id',
        'score',
        'negative_marks',
        'position',
        'correct_count',
        'incorrect_count',
        'skipped_count',
        'question_count',
        'detailed_results',
        'is_passed',
        'deleted_at',
    ];
}
