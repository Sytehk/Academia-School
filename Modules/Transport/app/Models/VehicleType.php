<?php

namespace Modules\Transport\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleType extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $table = 'vehicle_types';

    protected $fillable = ['institute_id', 'branch_id', 'name', 'status'];
}
