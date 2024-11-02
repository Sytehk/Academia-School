<?php

namespace Modules\ParentModule\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Academic\Database\Factories\ParentModelFactory;
use Modules\Academic\Models\Student;
use Modules\Authentication\Models\User;

class ParentModel extends Model
{
    use BelongsToTenant;
    use HasFactory, SoftDeletes;

    protected $table = 'parent_models'; // or your table name

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'institute_id',
        'branch_id',
        'user_id',
        'student_id',
        'parent_name',
        'parent_profession',
        'parent_phone',
        'present_address',
        'permanent_address',
        'access_key',
        'status',
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id')->select(
            'id',
            'name',
            'email',
            'image',
            'facebook',
            'twitter',
            'linkedin',
            'google_plus',
        );
    }

    public function student(): HasOne
    {
        return $this->hasOne(Student::class, 'id', 'student_id')->with('studentSession', 'user');
    }

    protected static function newFactory()
    {
        return ParentModelFactory::new();
    }
}
