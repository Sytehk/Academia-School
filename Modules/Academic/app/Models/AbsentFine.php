<?php

namespace Modules\Academic\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbsentFine extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = ['institute_id', 'branch_id', 'institute_id', 'branch_id', 'class_id', 'period_id', 'fee_amount'];

    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'class_id')->select('id', 'class_name');
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class, 'period_id')->select('id', 'period');
    }
}
