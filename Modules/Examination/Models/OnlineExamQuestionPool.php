<?php

namespace Modules\Examination\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A group of an online exam's questions from which each student is dealt
 * `draw_count` at random (e.g. "10 of these 40 algebra questions").
 */
class OnlineExamQuestionPool extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'institute_id',
        'branch_id',
        'online_exam_id',
        'name',
        'draw_count',
    ];

    protected $casts = [
        'draw_count' => 'integer',
    ];

    public function onlineExam(): BelongsTo
    {
        return $this->belongsTo(OnlineExam::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(OnlineExamQuestion::class, 'pool_id');
    }
}
