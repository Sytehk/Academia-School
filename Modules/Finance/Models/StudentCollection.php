<?php

namespace Modules\Finance\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Academic\Models\Student;
use Modules\Authentication\Models\User;

class StudentCollection extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'institute_id',
        'branch_id',
        'student_id',
        'class_id',
        'invoice_id',
        'invoice_date',
        'session_id',
        'ledger_id',
        'receive_ledger_id',
        'fund_id',
        'attendance_fine',
        'quiz_fine',
        'lab_fine',
        'tc_amount',
        'total_payable',
        'total_paid',
        'total_due',
        'note',
        'created_by',
        'updated_by',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id')->with('studentSession');
    }

    public function details(): HasMany
    {
        return $this->hasMany(StudentCollectionDetails::class, 'student_collection_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
