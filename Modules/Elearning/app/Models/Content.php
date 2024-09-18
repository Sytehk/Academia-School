<?php

namespace Modules\Elearning\Models;

use App\Traits\BelongsToInstitute;
use Illuminate\Database\Eloquent\Model;
use Modules\QuestionBank\Models\Quiz;

class Content extends Model
{
    use BelongsToInstitute;

    protected $fillable = [
        'institute_id',
        'chapter_id',
        'type',
        'type_id',
        'serial',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public const TABLE_NAME = 'contents';

    protected $table = self::TABLE_NAME;

    public function contentable()
    {
        return $this->morphTo(__FUNCTION__, 'type', 'type_id');
    }

    public static function nextSerial($chapterId)
    {
        $maxSerial = self::where('chapter_id', $chapterId)->max('serial');

        return ($maxSerial ?? 0) + 1;
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class, 'type_id')->where('type', 'lesson');
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'type_id')->where('type', 'quiz');
    }
}
