<?php

namespace Modules\Payroll\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Authentication\Models\User;

class UserPayroll extends Model
{
    use BelongsToTenant, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'institute_id',
        'branch_id',
        'user_id',
        'net_salary',
        'current_due',
        'current_advance',
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id')->select('id', 'name', 'phone', 'user_type', 'image');
    }

    public function payslipSalaries(): HasMany
    {
        return $this->hasMany(PayslipSalary::class, 'user_id', 'user_id');
    }
}
