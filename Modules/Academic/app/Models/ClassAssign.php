<?php

namespace Modules\Academic\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassAssign extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'class_assigns';

    protected $fillable = ['institute_id', 'branch_id', 'teacher_id', 'class_id'];

    /**
     * Relationship: ClassAssign belongs to a Teacher
     */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    /**
     * Relationship: ClassAssign belongs to a Class
     */
    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }
}
