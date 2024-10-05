<?php

namespace Modules\Student\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Academic\Models\Assignment;

class AssignmentSubmit extends Model
{
    use BelongsToTenant;
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'institute_id',
        'branch_id',
        'session_id',
        'assignment_id',
        'student_id',
        'file',
        'file_2',
        'file_3',
        'file_4',
        'result',
        'reviewed_by',
        'review_description',
        'reviewed_by',
    ];

    public function assignment(): HasOne
    {
        return $this->hasOne(Assignment::class, 'id', 'assignment_id');
    }
}
