<?php

namespace Modules\SMS\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Academic\Models\ClassModel;
use Modules\Academic\Models\Section;
use Modules\Academic\Models\Student;

class PhoneBook extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $fillable = [
        'institute_id',
        'branch_id',
        'name',
        'phone',
        'note',
        'category_id',
        'student_id',
        'class_id',
        'section_id',
        'source',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(PhoneBookCategory::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }
}
