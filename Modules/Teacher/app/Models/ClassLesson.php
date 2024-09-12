<?php

namespace Modules\Teacher\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Academic\Models\ClassModel;
use Modules\Academic\Models\Section;
use Modules\Academic\Models\Subject;

/**
 * A lesson / lesson plan for one class section and subject: term, week and
 * topic, objectives, activities and evaluation, attachments, and a draft →
 * published status that decides whether students and parents can see it.
 */
class ClassLesson extends Model
{
    use BelongsToTenant;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    protected $guarded = ['id'];

    protected $casts = [
        'published_at' => 'datetime',
        'term_number' => 'integer',
        'week_number' => 'integer',
        'duration_minutes' => 'integer',
    ];

    public function attachments(): HasMany
    {
        return $this->hasMany(ClassLessonAttachment::class, 'class_lesson_id');
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }
}
