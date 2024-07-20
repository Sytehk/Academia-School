<?php

namespace Modules\Examination\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Academic\Models\ClassModel;
use Modules\Academic\Models\Exam;

class GrandFinalClassExam extends Model
{
    use BelongsToTenant;

    protected $fillable = ['institute_id', 'branch_id', 'session_id', 'class_id', 'exam_id', 'percentage', 'serial_no'];

    protected $casts = [
        'percentage' => 'decimal:2',
        'serial_no' => 'integer',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class);
    }
}
