<?php

namespace Modules\Elearning\Models;

use App\Traits\BelongsToInstitute;
use Illuminate\Database\Eloquent\Model;
use Modules\Academic\Models\Teacher;

class ClassRoutine extends Model
{
    use BelongsToInstitute;

    protected $fillable = [
        'institute_id',
        'course_id',
        'teacher_id',
        'room_id',
        'start_time',
        'end_time',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public const TABLE_NAME = 'lms_class_routines';

    protected $table = self::TABLE_NAME;

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id')->select('id', 'title');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id')->select('id', 'name');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id')->select('id', 'name');
    }

    public function days()
    {
        return $this->belongsToMany(Day::class, 'class_routine_days', 'routine_id', 'day_id');
    }
}
