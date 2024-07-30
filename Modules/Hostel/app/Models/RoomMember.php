<?php

namespace Modules\Hostel\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Academic\Models\Student;

class RoomMember extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'institute_id',
        'branch_id',
        'room_id',
        'student_id',
        'bed_id',
        'start_date',
        'end_date',
        'status',
        'transfer_reason',
    ];

    public function bed()
    {
        return $this->belongsTo(Bed::class, 'bed_id');
    }

    public function collections()
    {
        return $this->hasMany(HostelCollection::class, 'room_member_id');
    }

    public function leaves()
    {
        return $this->hasMany(HostelLeave::class, 'room_member_id');
    }

    /**
     * The student assigned to this room member.
     */
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id')
            ->with('user:id,name,email,phone'); // eager load user info
    }

    /**
     * The room this member belongs to.
     */
    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id', 'id')
            ->with('hostelCategory:id,standard'); // eager load hostel category
    }
}
