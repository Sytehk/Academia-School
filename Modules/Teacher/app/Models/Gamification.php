<?php

namespace Modules\Teacher\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Modules\Academic\Models\ClassModel;
use Modules\Academic\Models\Section;
use Modules\Academic\Models\Student;

class Gamification extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'institute_id',
        'branch_id',
        'title',
        'session_id',
        'date',
        'student_id',
        'class_id',
        'section_id',
        'note',
        'file',
    ];

    protected $casts = [
        'student_id' => 'integer',
        'class_id' => 'integer',
        'section_id' => 'integer',
        'session_id' => 'integer',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }
}
