<?php

namespace Modules\Elearning\Models;

use App\Traits\BelongsToInstitute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Elearning\Database\Factories\LessonFactory;

class Lesson extends Model
{
    use BelongsToInstitute;
    use HasFactory, SoftDeletes;

    public const TABLE_NAME = 'lessons';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'institute_id',
        'course_id',
        'chapter_id',
        'title',
        'slug',
        'description',
        'thumbnail_image',

        // Video Handling
        'video_type',
        'video_url',
        'embedded_url',
        'uploaded_video_path',
        'document_file',
        'attachments',
        'priority',

        // Playback
        'playback_hours',
        'playback_minutes',
        'playback_seconds',

        // Scheduling
        'is_scheduled',
        'scheduled_at',

        // Visibility
        'visibility',
        'password',

        // Status & Meta
        'status',
        'created_by',
    ];

    protected $casts = [
        'attachments' => 'array',
    ];

    public function contents()
    {
        return $this->morphMany(Content::class, 'contentable', 'type', 'type_id');
    }

    public function visibilities()
    {
        return $this->hasMany(ContentVisibility::class, 'content_id');
    }

    protected static function newFactory(): LessonFactory
    {
        return LessonFactory::new();
    }
}
