<?php

namespace Modules\Academic\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceControl extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['student_id', 'device_access_type', 'device_limit', 'is_active'];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
