<?php

namespace Modules\Hostel\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Academic\Models\Student;
use Modules\Authentication\Models\User;

class HostelCollection extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'institute_id', 'branch_id', 'student_id', 'room_member_id',
        'invoice_id', 'invoice_date', 'session_id', 'month',
        'discount', 'fine', 'total_payable', 'total_paid', 'total_due',
        'note', 'ledger_id', 'receive_ledger_id', 'fund_id', 'created_by', 'updated_by',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id')->with('user:id,name,email,phone');
    }

    public function roomMember()
    {
        return $this->belongsTo(RoomMember::class, 'room_member_id');
    }

    public function details()
    {
        return $this->hasMany(HostelCollectionDetail::class, 'hostel_collection_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
