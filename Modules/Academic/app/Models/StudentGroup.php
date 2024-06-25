<?php

namespace Modules\Academic\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentGroup extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $table = 'student_groups';

    protected $guarded = ['id'];

    public function sections(): HasMany
    {
        // student_groups has no section_id column — the real FK direction is
        // sections.student_group_id -> student_groups.id (see Section::group()).
        return $this->hasMany(Section::class, 'student_group_id', 'id');
    }
}
