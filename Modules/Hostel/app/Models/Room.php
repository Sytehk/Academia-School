<?php

namespace Modules\Hostel\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use BelongsToTenant, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'institute_id',
        'branch_id',
        'hostel_category_id',
        'hostel_id',
        'building_id',
        'floor_id',
        'room_number',
        'capacity',
    ];

    public function hostelCategory()
    {
        return $this->belongsTo(HostelCategory::class, 'hostel_category_id');
    }

    public function hostel()
    {
        return $this->belongsTo(Hostel::class, 'hostel_id');
    }

    public function building()
    {
        return $this->belongsTo(HostelBuilding::class, 'building_id');
    }

    public function floor()
    {
        return $this->belongsTo(HostelFloor::class, 'floor_id');
    }

    public function beds()
    {
        return $this->hasMany(Bed::class, 'room_id');
    }
}
