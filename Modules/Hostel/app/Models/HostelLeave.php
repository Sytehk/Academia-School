<?php

namespace Modules\Hostel\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Modules\Academic\Models\Student;

class HostelLeave extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'institute_id', 'branch_id', 'student_id', 'room_member_id',
        'leave_start', 'leave_end', 'reason', 'status',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id')->with('user:id,name,email,phone');
    }

    public function roomMember()
    {
        return $this->belongsTo(RoomMember::class, 'room_member_id');
    }
}
