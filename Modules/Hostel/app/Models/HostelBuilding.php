<?php

namespace Modules\Hostel\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HostelBuilding extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = ['institute_id', 'branch_id', 'hostel_id', 'building_name'];

    public function hostel()
    {
        return $this->belongsTo(Hostel::class, 'hostel_id');
    }

    public function floors()
    {
        return $this->hasMany(HostelFloor::class, 'building_id');
    }
}
