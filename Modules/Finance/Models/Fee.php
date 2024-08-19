<?php

namespace Modules\Finance\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Academic\Models\ClassModel;
use Modules\Academic\Models\Section;
use Modules\Academic\Models\StudentCategory;
use Modules\Academic\Models\StudentGroup;

class Fee extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = ['institute_id', 'branch_id', 'class_id', 'session_id', 'section_id', 'group_id', 'student_category_id', 'fee_head_id', 'fee_amount', 'fine_amount', 'fund_id'];

    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'class_id')->select('id', 'class_name');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id')->select('id', 'section_name');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(StudentGroup::class, 'group_id')->select('id', 'group_name');
    }

    public function studentCategory(): BelongsTo
    {
        return $this->belongsTo(StudentCategory::class, 'student_category_id')->select('id', 'name');
    }

    public function feeHead(): BelongsTo
    {
        return $this->belongsTo(FeeHead::class, 'fee_head_id')->select('id', 'name');
    }
}
