<?php

namespace Modules\Hostel\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Modules\Academic\Models\Student;

class MealPlan extends Model
{
    use BelongsToTenant;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'institute_id',
        'branch_id',
        'student_id',
        'meal_id',
        'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id')
            ->with('user:id,name,email,phone'); // eager load user info
    }

    /**
     * The meal associated with this meal plan.
     */
    public function meal()
    {
        return $this->belongsTo(Meal::class, 'meal_id', 'id')
            ->select('id', 'meal_name', 'meal_type'); // optional: select only needed columns
    }
}
