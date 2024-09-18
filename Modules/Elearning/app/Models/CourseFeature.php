<?php

namespace Modules\Elearning\Models;

use App\Traits\BelongsToInstitute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Elearning\Database\Factories\CourseFeatureFactory;

class CourseFeature extends Model
{
    use BelongsToInstitute;
    use HasFactory, SoftDeletes;

    public const TABLE_NAME = 'course_features';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'institute_id',
        'course_id',
        'name',
        'priority',
        'status',
        'created_by',
    ];

    protected static function newFactory(): CourseFeatureFactory
    {
        return CourseFeatureFactory::new();
    }
}
