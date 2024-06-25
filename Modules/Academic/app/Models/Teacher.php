<?php

namespace Modules\Academic\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Academic\Database\Factories\TeacherFactory;
use Modules\Authentication\Models\User;

class Teacher extends Model
{
    use BelongsToTenant;
    use HasFactory, SoftDeletes;

    protected $table = 'teachers';

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
        'marital_status',
        'father_name',
        'mother_name',
        'nationality',
        'national_id',
        'spouse_name',
        'passport_no',
        'tin_no',
        'address',
        'joining_date',
        'permanent_address',
        'access_key',
        'attachment',
        'training_attachment',
        'is_administrator',
        'is_visible_website',
        'status',
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id')->select(
            'id',
            'name',
            'email',
            'phone',
            'image',
            'status',
            'facebook',
            'twitter',
            'linkedin',
            'google_plus',
        );
    }

    public function department(): HasOne
    {
        return $this->hasOne(Department::class, 'id', 'department_id');
    }

    protected static function newFactory()
    {
        return TeacherFactory::new();
    }
}
