<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Academic\Models\ClassModel;
use Modules\Academic\Models\Section;
use Modules\Academic\Models\Teacher;
use Modules\Authentication\Models\User;

class ZoomMeeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'institute_id',
        'branch_id',
        'meeting_id',
        'teacher_id',
        'class_id',
        'section_id',
        'topic',
        'agenda',
        'start_time',
        'duration',
        'password',
        'join_url',
        'start_url',
        'created_by',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /*
    |--------------------------------------------------------------------------
    | Teacher
    |--------------------------------------------------------------------------
    */
    public function teacher()
    {
        return $this->belongsTo(
            Teacher::class,
            'teacher_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Class
    |--------------------------------------------------------------------------
    */

    public function class()
    {
        return $this->belongsTo(
            ClassModel::class,
            'class_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Section
    |--------------------------------------------------------------------------
    */

    public function section()
    {
        return $this->belongsTo(
            Section::class,
            'section_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Created User
    |--------------------------------------------------------------------------
    */
    public function createdBy()
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }
}
