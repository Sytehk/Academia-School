<?php

namespace Modules\Examination\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Academic\Models\ClassModel;
use Modules\Academic\Models\Exam;
use Modules\Academic\Models\Student;
use Modules\Academic\Models\StudentGroup;
use Modules\Academic\Models\Subject;

class ExamMark extends Model
{
    use BelongsToTenant;

    public const STATUS_PASS = 'pass';

    public const STATUS_FAIL = 'fail';

    public const STATUS_ABSENT = 'absent';

    public const STATUS_EXEMPTED = 'exempted';

    public const SOURCE_MANUAL = 'manual';

    public const SOURCE_ONLINE = 'online';

    public const SOURCE_IMPORT = 'import';

    protected $fillable = [
        'institute_id',
        'branch_id',
        'session_id',
        'student_id',
        'class_id',
        'group_id',
        'subject_id',
        'exam_id',
        'mark1',
        'mark2',
        'mark3',
        'mark4',
        'mark5',
        'mark6',
        'total_marks',
        'percentage',
        'grade',
        'grade_point',
        'status',
        'remarks',
        'is_absent',
        'is_exempted',
        'source',
    ];

    protected $casts = [
        'mark1' => 'decimal:2',
        'mark2' => 'decimal:2',
        'mark3' => 'decimal:2',
        'mark4' => 'decimal:2',
        'mark5' => 'decimal:2',
        'mark6' => 'decimal:2',
        'total_marks' => 'decimal:2',
        'percentage' => 'decimal:2',
        'grade_point' => 'decimal:2',
        'is_absent' => 'boolean',
        'is_exempted' => 'boolean',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class)->with('studentSession')->select('id', 'first_name', 'last_name');
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'class_id', 'id')->select('id', 'class_name');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(StudentGroup::class, 'group_id')->select('id', 'group_name');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class)->select('id', 'subject_name');
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class)->select('id', 'session_id', 'name');
    }
}
