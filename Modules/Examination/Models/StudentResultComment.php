<?php

namespace Modules\Examination\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/** Class-teacher and principal / head-teacher comment on one student's term result. */
class StudentResultComment extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'institute_id',
        'branch_id',
        'session_id',
        'exam_id',
        'class_id',
        'student_id',
        'teacher_comment',
        'teacher_commented_by',
        'principal_comment',
        'principal_commented_by',
    ];
}
