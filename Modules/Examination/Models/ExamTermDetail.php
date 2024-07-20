<?php

namespace Modules\Examination\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Academic\Models\Exam;

/**
 * Term information printed on report cards: the term's dates (which bound the
 * attendance count), days school opened, and when/at what fee the next term
 * begins.
 */
class ExamTermDetail extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'institute_id',
        'branch_id',
        'session_id',
        'exam_id',
        'term_number',
        'term_start_date',
        'term_end_date',
        'days_school_opened',
        'next_term_begins',
        'next_term_fee',
        'next_term_note',
    ];

    protected $casts = [
        'term_start_date' => 'date',
        'term_end_date' => 'date',
        'next_term_begins' => 'date',
        'next_term_fee' => 'decimal:2',
        'days_school_opened' => 'integer',
        'term_number' => 'integer',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }
}
