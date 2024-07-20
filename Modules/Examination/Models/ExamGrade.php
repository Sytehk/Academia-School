<?php

namespace Modules\Examination\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Modules\Academic\Models\ClassModel;

class ExamGrade extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'institute_id',
        'branch_id',
        'session_id',
        'class_model_id',
        'grade_id',
        'grade_name',
        'grade_number',
        'grade_point',
        'grade_range',
        'number_high',
        'number_low',
        'point_high',
        'point_low',
        'priority',
    ];

    protected $casts = [
        'grade_number' => 'decimal:2',
        'grade_point' => 'decimal:2',
        'number_high' => 'decimal:2',
        'number_low' => 'decimal:2',
        'point_high' => 'decimal:2',
        'point_low' => 'decimal:2',
    ];

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_model_id', 'id');
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class, 'grade_id', 'id');
    }
}
