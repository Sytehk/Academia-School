<?php

namespace Modules\Transport\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class VehicleMaintenanceLog extends Model
{
    use BelongsToTenant;

    protected $table = 'vehicle_maintenance_logs';

    protected $fillable = [
        'institute_id', 'branch_id', 'bus_id', 'service_date',
        'service_type', 'cost_amount', 'vendor', 'note',
    ];

    public function bus()
    {
        return $this->belongsTo(Bus::class, 'bus_id');
    }
}
