<?php

namespace Modules\Hostel\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HostelFloor extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = ['institute_id', 'branch_id', 'building_id', 'floor_name'];

    public function building()
    {
        return $this->belongsTo(HostelBuilding::class, 'building_id');
    }

    public function rooms()
    {
        return $this->hasMany(Room::class, 'floor_id');
    }
}
