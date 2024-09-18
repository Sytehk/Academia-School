<?php

namespace Modules\Elearning\Models;

use App\Traits\BelongsToInstitute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Elearning\Database\Factories\ChapterFactory;
use Modules\QuestionBank\Models\Quiz;

class Chapter extends Model
{
    use BelongsToInstitute;
    use HasFactory, SoftDeletes;

    public const TABLE_NAME = 'chapters';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'institute_id',
        'course_id',
        'title',
        'slug',
        'priority',
        'image',
        'description',
        'meta_description',
        'meta_keywords',
        'status',
        'created_by',
    ];

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class, 'chapter_id', 'id')->select('id', 'chapter_id', 'title', 'priority');
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class, 'chapter_id', 'id')->select('id', 'chapter_id', 'title');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class, 'chapter_id', 'id')->select('id', 'chapter_id', 'title');
    }

    public function contents()
    {
        return $this->hasMany(Content::class)->orderBy('serial');
    }

    protected static function newFactory(): ChapterFactory
    {
        return ChapterFactory::new();
    }
}
