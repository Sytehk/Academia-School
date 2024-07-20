<?php

namespace Modules\Examination\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Academic\Models\ClassModel;

class GrandFinalSetting extends Model
{
    use BelongsToTenant;

    public const METHOD_WEIGHTED_PERCENTAGE = 'weighted_percentage';

    public const METHOD_FULL_MARKS = 'full_marks';

    protected $fillable = ['institute_id', 'branch_id', 'session_id', 'class_id', 'calculation_method'];

    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class);
    }
}
