<?php

namespace Modules\Elearning\Models;

use App\Traits\BelongsToInstitute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourseCategory extends Model
{
    use BelongsToInstitute;
    use HasFactory, SoftDeletes;

    public const TABLE_NAME = 'course_categories';

    protected $fillable = [
        'institute_id',
        'name',
        'slug',
        'image',
        'bg_color',
        'priority',
        'enable_homepage',
        'description',
        'parent_id',
        'status',
        'created_at',
        'updated_at',
    ];

    // A course category belongs to a parent category (if exists)
    public function parentCategory(): BelongsTo
    {
        return $this->belongsTo(CourseCategory::class, 'parent_id');
    }

    // A course category can have many sub-categories (children)
    public function subCategories(): HasMany
    {
        return $this->hasMany(CourseCategory::class, 'parent_id');
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'course_category_id', 'id');
    }
}
