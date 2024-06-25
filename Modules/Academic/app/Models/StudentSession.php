<?php

namespace Modules\Academic\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Examination\Models\ExamMark;

class StudentSession extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $table = 'student_sessions';

    protected $fillable = ['institute_id', 'branch_id', 'session_id', 'student_id', 'class_id', 'section_id', 'group_id', 'roll', 'qr_code'];

    /**
     * Demographic/profile fields only. Never use this relation for roll or group —
     * those are session-specific and must come from this StudentSession row itself
     * (->roll) or the group() relation below (->group_id / ->group).
     */
    public function student(): HasOne
    {
        return $this->hasOne(Student::class, 'id', 'student_id')
            ->select(
                'id',
                'user_id',
                'student_category_id',
                'first_name',
                'last_name',
                'phone',
                'registration_no',
                'father_name',
                'mother_name',
                'birthday',
                'gender',
                'blood_group',
                'religion',
                'address',
                'nationality',
                'birth_certificate_no',
                'nid_no',
                'date_of_admission',
                'tc_date',
                'status',
            )
            ->with('user', 'studentCategory');
    }

    public function session(): HasOne
    {
        return $this->hasOne(AcademicYear::class, 'id', 'session_id');
    }

    public function class(): HasOne
    {
        return $this->hasOne(ClassModel::class, 'id', 'class_id');
    }

    public function section(): HasOne
    {
        return $this->hasOne(Section::class, 'id', 'section_id');
    }

    /**
     * Canonical way to resolve a student's group for this session:
     * $studentSession->group_id / $studentSession->group?->group_name.
     */
    public function group(): HasOne
    {
        return $this->hasOne(StudentGroup::class, 'id', 'group_id');
    }

    public function examMarks(): HasMany
    {
        return $this->hasMany(ExamMark::class, 'student_id', 'student_id');
    }

    public function optionalSubjects()
    {
        return $this->hasMany(StudentOptionalSubject::class);
    }
}
