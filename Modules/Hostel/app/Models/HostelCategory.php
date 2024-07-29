<?php

namespace Modules\Hostel\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class HostelCategory extends Model
{
    use BelongsToTenant;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'institute_id',
        'branch_id',
        'hostel_id',
        'standard',
        'hostel_fee',
        'note',
    ];

    public function hostel()
    {
        return $this->belongsTo(
            Hostel::class,
            'hostel_id'
        );
    }
}
