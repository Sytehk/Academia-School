<?php

namespace Modules\Academic\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Academic\Database\Factories\StaffFactory;
use Modules\Authentication\Models\User;

class Staff extends Model
{
    use BelongsToTenant;
    use HasFactory, SoftDeletes;

    protected $table = 'staffs';

    protected $fillable = [
        'institute_id',
        'branch_id',
        'user_id',
        'department_id',
        'name',
        'designation',
        'birthday',
        'gender',
        'religion',
        'phone',
        'blood',
        'sl',
        'address',
        'joining_date',
        'access_key',
        'status',
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    protected static function newFactory()
    {
        return StaffFactory::new();
    }
}
