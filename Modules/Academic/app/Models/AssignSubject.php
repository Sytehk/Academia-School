<?php

namespace Modules\Academic\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class AssignSubject extends Model
{
    use BelongsToTenant;

    protected $guarded = ['id'];

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    /**
     * Get subjects with existing teacher assignment (if any)
     */
    public static function getSubjects($classId, $sectionId)
    {
        return Subject::query()
            ->where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->where('class_id', $classId)
            ->with([
                'assign' => function ($q) use ($sectionId) {
                    $q->where('section_id', $sectionId);
                },
            ])
            ->get();
    }

    public function assign()
    {
        return $this->hasOne(self::class, 'subject_id', 'id');
    }
}
