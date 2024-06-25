<?php

namespace Modules\Academic\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentMigration extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $fillable = [
        'institute_id',
        'branch_id',
        'student_id',
        'type',
        'class_id',
        'section_id',
        'group_id',
        'prev_class_id',
        'prev_section_id',
        'prev_group_id',
        'prev_roll',
        'new_roll',
        'academic_year',
    ];

    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'prev_class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'prev_section_id');
    }
}
