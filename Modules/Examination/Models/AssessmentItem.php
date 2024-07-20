<?php

namespace Modules\Examination\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentItem extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'institute_id',
        'branch_id',
        'assessment_domain_id',
        'name',
        'max_score',
        'rating_type',
        'grade_scale',
        'remarks_enabled',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'max_score' => 'decimal:2',
        'grade_scale' => 'array',
        'remarks_enabled' => 'boolean',
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    public function domain(): BelongsTo
    {
        return $this->belongsTo(AssessmentDomain::class, 'assessment_domain_id');
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(StudentAssessment::class);
    }
}
