<?php

namespace Modules\Finance\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentCollectionDetailsSubHead extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'institute_id',
        'branch_id',
        'student_id',
        'session_id',
        'student_collection_id',
        'student_collection_details_id',
        'fee_head_id',
        'sub_head_id',
        'total_amount',
        'paid_amount',
        'due_amount',
    ];

    public function feeSubHead(): BelongsTo
    {
        return $this->belongsTo(FeeSubHead::class, 'sub_head_id');
    }

    public function feeHead(): BelongsTo
    {
        return $this->belongsTo(FeeHead::class, 'fee_head_id');
    }

    public function collectionDetail(): BelongsTo
    {
        return $this->belongsTo(StudentCollectionDetails::class, 'student_collection_details_id');
    }
}
