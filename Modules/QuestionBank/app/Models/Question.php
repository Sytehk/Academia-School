<?php

namespace Modules\QuestionBank\Models;

use App\Traits\BelongsToInstitute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\QuestionBank\Database\Factories\QuestionFactory;

class Question extends Model
{
    use BelongsToInstitute, HasFactory, SoftDeletes;

    public const TABLE_NAME = 'questions';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'institute_id',
        'question_bank_class_id',
        'question_bank_group_id',
        'question_bank_subject_id',
        'question_bank_chapter_id',
        'question_category_id',
        'question_bank_difficulty_level_id',
        'type',
        'question_name',
        'question',
        'options',
        'correct_answer',
        'explanation',
        'marks',
        'negative_marks',
        'negative_marks_type',
        'image_file',
        'price',
        'question_year',
        'language',
        'status',
        'created_by',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'options' => 'array',
        'correct_answer' => 'array',
        'question_year' => 'array',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        // 'correct_answer',
    ];

    /**
     * Define the relationship with the QuestionCategory model.
     */
    public function questionCategory()
    {
        return $this->belongsTo(QuestionCategory::class, 'question_category_id')->select('id', 'name');
    }

    public function types(): BelongsToMany
    {
        return $this->belongsToMany(QuestionBankType::class, 'question_type');
    }

    public function levels(): BelongsToMany
    {
        return $this->belongsToMany(QuestionBankLevel::class, 'question_level');
    }

    public function topics(): BelongsToMany
    {
        return $this->belongsToMany(QuestionBankTopic::class, 'question_topic');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(QuestionBankTag::class, 'question_tag');
    }

    public function session(): BelongsToMany
    {
        return $this->belongsToMany(QuestionBankSession::class, 'question_session');
    }

    public function sources(): BelongsToMany
    {
        return $this->belongsToMany(QuestionBankSource::class, 'question_source');
    }

    public function sub_sources(): BelongsToMany
    {
        return $this->belongsToMany(
            QuestionBankSubSource::class,
            'question_sub_source',
            'question_id',
            'question_bank_sub_source_id' // explicitly define the foreign pivot key name
        );
    }

    public function tests(): BelongsToMany
    {
        return $this->belongsToMany(QuestionBankTest::class, 'question_test');
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(QuestionBankClass::class, 'question_bank_class_id')->select('id', 'name');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(QuestionBankGroup::class, 'question_bank_group_id')->select('id', 'name');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(QuestionBankSubject::class, 'question_bank_subject_id')->select('id', 'name');
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(QuestionBankChapter::class, 'question_bank_chapter_id')->select('id', 'subject_id', 'name');
    }

    public function difficulty(): BelongsTo
    {
        return $this->belongsTo(QuestionBankDifficultyLevel::class, 'question_bank_difficulty_level_id')->select('id', 'level_name', 'level_code');
    }

    protected static function newFactory(): QuestionFactory
    {
        return QuestionFactory::new();
    }
}
