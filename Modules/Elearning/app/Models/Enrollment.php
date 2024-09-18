<?php

namespace Modules\Elearning\Models;

use App\Traits\BelongsToInstitute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Authentication\Models\User;

class Enrollment extends Model
{
    use BelongsToInstitute;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'institute_id',
        'user_id',
        'course_id',
        'enrollment_type',
        'enrollment_date',
        'subscription_start',
        'subscription_end',
        'amount_paid',
        'status',
    ];

    // Optionally, if you need to handle soft deletes
    protected $dates = [
        'deleted_at',
        'subscription_start',
        'subscription_end',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class)->with('author', 'courseCategory', 'courseSubCategory');
    }
}
