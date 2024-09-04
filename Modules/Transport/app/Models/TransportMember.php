<?php

namespace Modules\Transport\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Modules\Academic\Models\Student;

class TransportMember extends Model
{
    use BelongsToTenant;

    protected $table = 'transport_members';

    public const TABLE_NAME = 'transport_members';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'institute_id',
        'branch_id',
        'student_id',
        'assigned_route_id',
        'assigned_stop_id',
        'seat_id',
        'transport_id',
        'fare_amount',
        'status',
    ];

    public function route()
    {
        return $this->belongsTo(BusRoute::class, 'assigned_route_id');
    }

    public function stop()
    {
        return $this->belongsTo(BusStop::class, 'assigned_stop_id');
    }

    public function seat()
    {
        return $this->belongsTo(BusSeat::class, 'seat_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function collections()
    {
        return $this->hasMany(TransportCollection::class, 'transport_member_id');
    }
}
