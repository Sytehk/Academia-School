<?php

namespace Modules\Examination\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class MarkConfigExamCode extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'institute_id',
        'branch_id',
        'session_id',
        'subject_id',
        'title',
        'total_marks',
        'pass_mark',
        'acceptance',
    ];

    protected $casts = [
        'total_marks' => 'decimal:2',
        'pass_mark' => 'decimal:2',
        'acceptance' => 'decimal:2',
    ];
}
