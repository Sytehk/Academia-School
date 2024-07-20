<?php

namespace Modules\Examination\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\QuestionBank\Models\Question;

class OnlineExamQuestion extends Model
{
    use BelongsToTenant;

    public const TYPE_MULTIPLE_CHOICE = 'multiple_choice';

    public const TYPE_MULTIPLE_SELECT = 'multiple_select';

    public const TYPE_TRUE_FALSE = 'true_false';

    public const TYPE_FILL_BLANK = 'fill_blank';

    public const TYPE_SHORT_ANSWER = 'short_answer';

    public const TYPE_DESCRIPTIVE = 'descriptive';

    /** A list of true/false statements; `correct_answer` holds one value per statement. */
    public const TYPE_MULTIPLE_TRUE_FALSE = 'multiple_true_false';

    public const TYPES = [
        self::TYPE_MULTIPLE_CHOICE,
        self::TYPE_MULTIPLE_SELECT,
        self::TYPE_TRUE_FALSE,
        self::TYPE_MULTIPLE_TRUE_FALSE,
        self::TYPE_FILL_BLANK,
        self::TYPE_SHORT_ANSWER,
        self::TYPE_DESCRIPTIVE,
    ];

    protected $fillable = [
        'institute_id',
        'branch_id',
        'online_exam_id',
        'pool_id',
        'question_id',
        'type',
        'question_text',
        'image',
        'options',
        'correct_answer',
        'explanation',
        'marks',
        'difficulty',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'options' => 'array',
        'correct_answer' => 'array',
        'marks' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function onlineExam(): BelongsTo
    {
        return $this->belongsTo(OnlineExam::class);
    }

    public function bankQuestion(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'question_id');
    }

    public function isAutoScorable(): bool
    {
        return in_array($this->type, [
            self::TYPE_MULTIPLE_CHOICE,
            self::TYPE_MULTIPLE_SELECT,
            self::TYPE_TRUE_FALSE,
            self::TYPE_MULTIPLE_TRUE_FALSE,
            self::TYPE_FILL_BLANK,
        ], true);
    }

    public function pool(): BelongsTo
    {
        return $this->belongsTo(OnlineExamQuestionPool::class, 'pool_id');
    }
}
