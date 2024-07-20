<?php

namespace Modules\Examination\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Academic\Models\ClassModel;
use Modules\Academic\Models\Exam;
use Modules\Academic\Models\StudentGroup;
use Modules\Academic\Models\Subject;

class MarkConfig extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'institute_id',
        'branch_id',
        'class_id',
        'group_id',
        'subject_id',
        'exam_id',
        'mark_config_exam_code_id',
        'session_id',
        'updated_at',
        'created_at',
    ];

    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'class_id')->select('id', 'class_name');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(StudentGroup::class, 'group_id')->select('id', 'group_name');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id')->select('id', 'class_id', 'group_id', 'subject_name');
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class, 'exam_id')->select('id', 'session_id', 'name');
    }

    public function mark_config_exam_code(): BelongsTo
    {
        return $this->belongsTo(MarkConfigExamCode::class, 'mark_config_exam_code_id', 'id');
    }
}
