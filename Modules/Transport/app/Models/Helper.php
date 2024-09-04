<?php

namespace Modules\Transport\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Helper extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $table = 'helpers';

    protected $fillable = [
        'institute_id',
        'branch_id',
        'name',
        'phone_number',
        'address',
        'emergency_contact_name',
        'emergency_contact_phone',
        'assigned_bus_id',
        'status',
    ];

    public function bus()
    {
        return $this->belongsTo(Bus::class, 'assigned_bus_id');
    }
}
