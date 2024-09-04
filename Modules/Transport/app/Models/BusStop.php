<?php

namespace Modules\Transport\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class BusStop extends Model
{
    use BelongsToTenant;

    protected $table = 'bus_stops';

    public const TABLE_NAME = 'bus_stops';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'institute_id',
        'branch_id',
        'route_id',
        'stop_name',
        'latitude',
        'longitude',
        'stop_order',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function busRoute()
    {
        return $this->belongsTo(BusRoute::class);
    }
}
