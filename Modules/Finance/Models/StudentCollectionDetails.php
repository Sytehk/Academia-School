<?php

namespace Modules\Finance\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentCollectionDetails extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'institute_id',
        'branch_id',
        'student_id',
        'session_id',
        'student_collection_id',
        'fee_head_id',
        'ledger_id',
        'total_due',
        'total_paid',
        'waiver',
        'fine_payable',
        'fee_payable',
        'fee_and_fine_payable',
        'fee_and_fine_paid',
        'previous_due_payable',
        'previous_due_paid',
        'total_payable',
    ];

    public function feeHead(): BelongsTo
    {
        return $this->belongsTo(FeeHead::class, 'fee_head_id');
    }

    public function subHeads(): HasMany
    {
        return $this->hasMany(StudentCollectionDetailsSubHead::class, 'student_collection_details_id');
    }
}
