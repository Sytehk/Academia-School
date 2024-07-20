<?php

namespace Modules\Examination\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Academic\Models\ClassModel;
use Modules\Academic\Models\Exam;

/**
 * Approval / publication state of one exam's results for one class.
 *
 * draft → submitted → approved → published. A locked publication freezes
 * every mark, assessment and comment for that exam + class; publishing locks.
 */
class ResultPublication extends Model
{
    use BelongsToTenant;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_PUBLISHED = 'published';

    protected $fillable = [
        'institute_id',
        'branch_id',
        'session_id',
        'exam_id',
        'class_id',
        'status',
        'is_locked',
        'submitted_by',
        'submitted_at',
        'approved_by',
        'approved_at',
        'published_by',
        'published_at',
        'locked_by',
        'locked_at',
    ];

    protected $casts = [
        'is_locked' => 'boolean',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'published_at' => 'datetime',
        'locked_at' => 'datetime',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ResultPublicationLog::class)->latest('id');
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }
}
