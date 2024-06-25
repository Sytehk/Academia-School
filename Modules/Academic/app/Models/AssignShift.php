<?php

namespace Modules\Academic\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignShift extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'assign_shifts';

    protected $fillable = ['institute_id', 'branch_id', 'teacher_id', 'shift_id'];

    /**
     * Relationship: AssignShift belongs to a Teacher
     */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    /**
     * Relationship: AssignShift belongs to a Shift
     */
    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }
}
