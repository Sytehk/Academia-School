<?php

namespace Modules\Academic\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** The promote / repeat / graduate decision taken for one student in a batch. */
class StudentPromotion extends Model
{
    use BelongsToTenant;

    public const PROMOTE = 'promote';

    public const REPEAT = 'repeat';

    public const GRADUATE = 'graduate';

    public const ACTIONS = [self::PROMOTE, self::REPEAT, self::GRADUATE];

    protected $fillable = [
        'institute_id',
        'branch_id',
        'batch_id',
        'student_id',
        'action',
        'suggested_action',
        'from_student_session_id',
        'to_student_session_id',
        'from_class_id',
        'from_section_id',
        'to_class_id',
        'to_section_id',
        'to_roll',
        'average',
        'failed_subjects',
        'note',
    ];

    protected $casts = [
        'average' => 'decimal:2',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(StudentPromotionBatch::class, 'batch_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
