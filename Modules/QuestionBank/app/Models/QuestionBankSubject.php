<?php

namespace Modules\QuestionBank\Models;

use App\Traits\SharedWithInstitute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionBankSubject extends Model
{
    use SharedWithInstitute;

    protected $fillable = [
        'name',
        'question_category_id',
        'class_id',
        'group_id',
        'slug',
        'code',
        'type',
        'color_code',
        'priority',
        'description',
        'icon',
        'image',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public const TABLE_NAME = 'question_bank_subjects';

    protected $table = self::TABLE_NAME;

    public function chapters(): HasMany
    {
        return $this->hasMany(QuestionBankChapter::class, 'subject_id', 'id');
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(QuestionBankSubject::class, 'id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(QuestionCategory::class, 'question_category_id');
    }
}
