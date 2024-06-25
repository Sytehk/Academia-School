<?php

namespace Modules\Academic\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentOptionalSubject extends Model
{
    use BelongsToTenant;
    use SoftDeletes;

    protected $fillable = [
        'institute_id',
        'branch_id',
        'student_id',
        'student_session_id',
        'optional_subject_config_id',
        'subject_id',
        'mark',
    ];

    public const TABLE_NAME = 'student_optional_subjects';

    protected $table = self::TABLE_NAME;

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function studentSession()
    {
        return $this->belongsTo(StudentSession::class);
    }

    public function config()
    {
        return $this->belongsTo(OptionalSubjectConfig::class, 'optional_subject_config_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
