<?php

namespace Modules\Elearning\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Authentication\Models\User;
use Modules\Elearning\Database\Factories\CourseFactory;
use Modules\QuestionBank\Models\Quiz;

class Course extends Model
{
    use BelongsToTenant;
    use HasFactory, SoftDeletes;

    public const TABLE_NAME = 'courses';

    protected $fillable = [
        'institute_id',
        'course_category_id',
        'course_sub_category_id',
        'title',
        'slug',
        'image',
        'video_type',
        'video_url',
        'embedded_url',
        'uploaded_video_path',
        'status',
        'publish_date',
        'type',
        'payment_type',
        'invoice_title',
        'regular_price',
        'offer_price',
        'repeat_count',
        'fake_enrolled_students',
        'total_classes',
        'total_notes',
        'total_exams',
        'payment_duration',
        'total_cycles',
        'is_infinity',
        'is_auto_generate_invoice',
        'description',
        'class_routine_image',
        'meta_description',
        'meta_keywords',
        'total_view',
        'total_enrolled',
        'created_by',
    ];

    protected $casts = [
        'regular_price' => 'decimal:2',
        'offer_price' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id')->select('id', 'name');
    }

    public function courseCategory(): BelongsTo
    {
        return $this->belongsTo(CourseCategory::class, 'course_category_id', 'id')->select('id', 'name');
    }

    public function courseSubCategory(): BelongsTo
    {
        return $this->belongsTo(CourseCategory::class, 'course_sub_category_id', 'id')->select('id', 'name');
    }

    public function courseFaqs(): HasMany
    {
        return $this->hasMany(CourseFaq::class, 'course_id', 'id')->select('id', 'course_id', 'question', 'answer');
    }

    public function courseFeatures(): HasMany
    {
        return $this->hasMany(CourseFeature::class, 'course_id', 'id')->select('id', 'course_id', 'name', 'priority');
    }

    public function courseChapters()
    {
        return $this->hasMany(Chapter::class)->with(['contents' => function ($q) {
            $q->orderBy('serial');
        }, 'contents.contentable'])->orderBy('priority');
    }

    public function courseQuizzes(): HasMany
    {
        return $this->hasMany(Quiz::class, 'course_id', 'id')->select('id', 'institute_id', 'course_id', 'chapter_id', 'title');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function zoomMeetings(): HasMany
    {
        return $this->hasMany(ZoomMeeting::class, 'course_id', 'id')->where('deleted_at', null);
    }

    protected static function newFactory(): CourseFactory
    {
        return CourseFactory::new();
    }
}
