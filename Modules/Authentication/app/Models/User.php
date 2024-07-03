<?php

namespace Modules\Authentication\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Modules\Academic\Models\Student;
use Modules\Academic\Models\Teacher;
use Modules\Payroll\Models\PayslipSalary;
use Modules\Payroll\Models\UserPayroll;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    public const TABLE_NAME = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'institute_id',
        'branch_id',
        'name',
        'username',
        'email',
        'password',
        'phone',
        'image',
        'role_id',
        'email_verified_at',
        'status',
        'user_type',
        'facebook',
        'twitter',
        'linkedin',
        'google_plus',
        'user_status',
        'nid',
        'platform',
        'device_info',
        'last_active_time',
        'created_by',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function institute(): HasOne
    {
        return $this->hasOne(Institute::class, 'id', 'institute_id')
            ->select('id', 'name', 'status')->with('subscription');
    }

    public function branch(): HasOne
    {
        return $this->hasOne(Branch::class, 'id', 'branch_id');
    }

    public function role(): HasOne
    {
        return $this->hasOne(Role::class, 'id', 'role_id');
    }

    public function teacher()
    {
        return $this->hasOne(Teacher::class, 'user_id');
    }

    public function student()
    {
        return $this->hasOne(Student::class, 'user_id');
    }

    public function userPayroll()
    {
        return $this->hasOne(UserPayroll::class, 'user_id', 'id');
    }

    public function payslipSalary()
    {
        return $this->hasMany(PayslipSalary::class, 'user_id', 'id');
    }

    public function payroll(): HasOne
    {
        return $this->hasOne(UserPayroll::class, 'user_id', 'id');
    }
}
