<?php

namespace Modules\Teacher\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassLessonAttachment extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'institute_id',
        'branch_id',
        'class_lesson_id',
        'file',
        'original_name',
        'mime',
        'size',
    ];

    protected $appends = ['url'];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(ClassLesson::class, 'class_lesson_id');
    }

    public function getUrlAttribute(): string
    {
        return asset('uploads/files/class_lessons/'.$this->file);
    }
}
