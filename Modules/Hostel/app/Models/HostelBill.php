<?php

namespace Modules\Hostel\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Modules\Academic\Models\Student;

class HostelBill extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'institute_id',
        'branch_id',
        'student_id',
        'hostel_fee',
        'meal_fee',
        'total_amount',
        'status',
        'due_date',
    ];

    /**
     * The student assigned to this room member.
     */
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id')
            ->with('user:id,name,email,phone'); // eager load user info
    }
}
