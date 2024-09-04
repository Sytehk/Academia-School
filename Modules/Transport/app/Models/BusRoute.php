<?php

namespace Modules\Transport\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class BusRoute extends Model
{
    use BelongsToTenant;

    protected $table = 'bus_routes';

    public const TABLE_NAME = 'bus_routes';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'institute_id',
        'branch_id',
        'route_name',
        'bus_id',
        'start_location',
        'end_location',
        'total_distance',
        'estimated_time',
        'status',
    ];

    public function stops()
    {
        return $this->hasMany(BusStop::class);
    }

    public function bus()
    {
        return $this->belongsTo(Bus::class, 'bus_id');
    }
}
