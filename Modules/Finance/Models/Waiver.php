<?php

namespace Modules\Finance\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Waiver extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = ['institute_id', 'branch_id', 'waiver', 'serial'];
}
