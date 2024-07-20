<?php

namespace Modules\Examination\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Academic\Models\ClassModel;
use Modules\Academic\Models\Exam;

class ClassExam extends Model
{
    use BelongsToTenant;

    protected $fillable = ['institute_id', 'branch_id', 'class_id', 'exam_id', 'merit_process_type_id', 'session_id'];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class)->select('id', 'session_id', 'name', 'exam_code');
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class)->select('id', 'class_name');
    }

    public function meritType(): BelongsTo
    {
        return $this->belongsTo(MeritProcessType::class, 'merit_process_type_id', 'id')->select('id', 'type', 'serial');
    }
}
