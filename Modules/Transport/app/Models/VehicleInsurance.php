<?php

namespace Modules\Transport\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class VehicleInsurance extends Model
{
    use BelongsToTenant;

    protected $table = 'vehicle_insurances';

    protected $fillable = [
        'institute_id', 'branch_id', 'bus_id', 'policy_number',
        'provider', 'start_date', 'expiry_date', 'premium_amount', 'document_path',
    ];

    public function bus()
    {
        return $this->belongsTo(Bus::class, 'bus_id');
    }
}
