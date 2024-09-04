<?php

namespace Modules\Transport\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class VehicleFuelLog extends Model
{
    use BelongsToTenant;

    protected $table = 'vehicle_fuel_logs';

    protected $fillable = [
        'institute_id', 'branch_id', 'bus_id', 'fill_date',
        'quantity_liters', 'cost_amount', 'odometer_reading', 'note',
    ];

    public function bus()
    {
        return $this->belongsTo(Bus::class, 'bus_id');
    }
}
