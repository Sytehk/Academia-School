<?php

namespace Modules\Transport\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    use BelongsToTenant;

    protected $table = 'drivers';

    public const TABLE_NAME = 'drivers';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'institute_id',
        'branch_id',
        'name',
        'phone_number',
        'license_number',
        'license_expiry_date',
        'photo',
        'address',
        'id_proof_path',
        'emergency_contact_name',
        'emergency_contact_phone',
        'assigned_bus_id',
        'status',
    ];

    public function bus()
    {
        return $this->belongsTo(Bus::class, 'assigned_bus_id');
    }

    public function documents()
    {
        return $this->hasMany(DriverDocument::class, 'driver_id');
    }
}
