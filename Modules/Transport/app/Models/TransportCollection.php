<?php

namespace Modules\Transport\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Academic\Models\Student;
use Modules\Authentication\Models\User;

class TransportCollection extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $table = 'transport_collections';

    protected $fillable = [
        'institute_id', 'branch_id', 'student_id', 'transport_member_id',
        'invoice_id', 'invoice_date', 'session_id', 'month',
        'fee_amount', 'discount', 'fine', 'total_payable', 'total_paid', 'total_due',
        'note', 'ledger_id', 'receive_ledger_id', 'fund_id', 'created_by', 'updated_by',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id')->with('studentSession');
    }

    public function transportMember()
    {
        return $this->belongsTo(TransportMember::class, 'transport_member_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
