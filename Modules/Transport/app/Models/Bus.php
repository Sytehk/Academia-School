<?php

namespace Modules\Transport\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Bus extends Model
{
    use BelongsToTenant;

    protected $table = 'buses';

    public const TABLE_NAME = 'buses';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'institute_id',
        'branch_id',
        'bus_number',
        'vehicle_type_id',
        'vehicle_category_id',
        'model',
        'registration_number',
        'capacity',
        'gps_device_id',
        'last_latitude',
        'last_longitude',
        'last_location_at',
        'status',
    ];

    public function driver()
    {
        return $this->hasOne(Driver::class, 'assigned_bus_id');
    }

    public function helper()
    {
        return $this->hasOne(Helper::class, 'assigned_bus_id');
    }

    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class, 'vehicle_type_id');
    }

    public function vehicleCategory()
    {
        return $this->belongsTo(VehicleCategory::class, 'vehicle_category_id');
    }

    public function seats()
    {
        return $this->hasMany(BusSeat::class, 'bus_id');
    }

    public function fuelLogs()
    {
        return $this->hasMany(VehicleFuelLog::class, 'bus_id');
    }

    public function maintenanceLogs()
    {
        return $this->hasMany(VehicleMaintenanceLog::class, 'bus_id');
    }

    public function insurance()
    {
        return $this->hasOne(VehicleInsurance::class, 'bus_id')->latestOfMany();
    }

    public function fitnessCertificate()
    {
        return $this->hasOne(VehicleFitnessCertificate::class, 'bus_id')->latestOfMany();
    }

    public function taxInfo()
    {
        return $this->hasOne(VehicleTaxInfo::class, 'bus_id')->latestOfMany();
    }
}
