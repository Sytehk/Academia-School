<?php

namespace Modules\Hostel\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Bed extends Model
{
    use BelongsToTenant;

    protected $fillable = ['institute_id', 'branch_id', 'room_id', 'bed_number', 'status'];

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function roomMember()
    {
        return $this->hasOne(RoomMember::class, 'bed_id')->where('status', 'active');
    }
}
