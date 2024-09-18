<?php

namespace Modules\Elearning\Models;

use App\Traits\BelongsToInstitute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Elearning\Database\Factories\CourseFaqFactory;

class CourseFaq extends Model
{
    use BelongsToInstitute;
    use HasFactory, SoftDeletes;

    public const TABLE_NAME = 'course_faqs';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'institute_id',
        'course_id',
        'question',
        'answer',
        'status',
        'created_by',
    ];

    protected static function newFactory(): CourseFaqFactory
    {
        return CourseFaqFactory::new();
    }
}
