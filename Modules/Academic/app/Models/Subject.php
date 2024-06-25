<?php

namespace Modules\Academic\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $guarded = ['id'];

    public function group(): BelongsTo
    {
        return $this->belongsTo(StudentGroup::class, 'group_id');
    }

    public function mergeConfig(): HasOne
    {
        return $this->hasOne(SubjectConfig::class, 'subject_id');
    }

    public static function getCommonSubjects($class_id)
    {
        return Subject::where('class_id', $class_id)
            ->where('group_id', null)
            ->where(function ($query) {
                $query->where('subject_type', 'Compulsory')
                    ->orWhere('subject_type', 'All');
            })
            ->orderBy('sl_no', 'ASC')
            ->get();
    }

    public static function getGroupWiseSubjects($class_id, $group_id)
    {
        return Subject::where('class_id', $class_id)
            ->where('group_id', $group_id)
            ->where(function ($query) {
                $query->where('subject_type', 'GROUP BASED')
                    ->orWhere('subject_type', 'All');
            })
            ->orderBy('sl_no', 'ASC')
            ->get();
    }

    public static function getCompulsorySubjects($class_id, $group_id)
    {
        $commonSubjectForClass = self::getCommonSubjects($class_id);
        $groupWiseSubjects = self::getGroupWiseSubjects($class_id, $group_id);

        return $commonSubjectForClass->merge($groupWiseSubjects);
    }

    public static function getElectiveOrOptionalSubjects($class_id, $group_id)
    {
        return Subject::where('class_id', $class_id)
            ->where('group_id', $group_id)
            ->where(function ($query) {
                $query->where('subject_type', 'CHOOSABLE');
            })
            ->orderBy('sl_no', 'ASC')
            ->get();
    }

    public function assign()
    {
        return $this->hasOne(AssignSubject::class, 'subject_id', 'id');
    }

    public function routines()
    {
        return $this->hasMany(ClassRoutine::class, 'subject_id');
    }

    public function optionalItems()
    {
        return $this->hasMany(OptionalSubjectItem::class);
    }
}
