<?php

namespace Modules\Hostel\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HostelFeeConfig extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'institute_id', 'branch_id', 'hostel_category_id', 'session_id',
        'admission_fee', 'monthly_fee', 'security_deposit', 'utility_charge',
        'maintenance_charge', 'fund_id', 'ledger_id',
    ];

    public function hostelCategory()
    {
        return $this->belongsTo(HostelCategory::class, 'hostel_category_id');
    }
}
