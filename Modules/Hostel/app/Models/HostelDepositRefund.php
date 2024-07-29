<?php

namespace Modules\Hostel\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Modules\Academic\Models\Student;

class HostelDepositRefund extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'institute_id', 'branch_id', 'hostel_collection_detail_id', 'student_id',
        'refund_amount', 'refund_date', 'reason', 'processed_by',
    ];

    public function detail()
    {
        return $this->belongsTo(HostelCollectionDetail::class, 'hostel_collection_detail_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
