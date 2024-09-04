<?php

namespace Modules\Transport\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class BusSeat extends Model
{
    use BelongsToTenant;

    protected $table = 'bus_seats';

    protected $fillable = ['institute_id', 'branch_id', 'bus_id', 'seat_number', 'status'];

    public function bus()
    {
        return $this->belongsTo(Bus::class, 'bus_id');
    }

    public function transportMember()
    {
        return $this->hasOne(TransportMember::class, 'seat_id');
    }
}
