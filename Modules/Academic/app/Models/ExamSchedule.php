<?php

namespace Modules\Academic\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class ExamSchedule extends Model
{
    use BelongsToTenant;

    protected $guarded = ['id'];

    protected $table = 'exam_schedules';

    // 🔹 Each schedule belongs to an exam
    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }

    // 🔹 Each schedule belongs to a class
    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id'); // adjust model name if yours is `ClassModel` or `AcademicClass`
    }

    // 🔹 Each schedule belongs to a subject
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    // 🔹 Each schedule may belong to a session (academic year)
    public function session()
    {
        return $this->belongsTo(StudentSession::class, 'session_id');
    }
}
