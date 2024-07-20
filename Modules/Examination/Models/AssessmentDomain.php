<?php

namespace Modules\Examination\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentDomain extends Model
{
    use BelongsToTenant;

    public const TYPE_COGNITIVE = 'cognitive';

    public const TYPE_AFFECTIVE = 'affective';

    public const TYPE_PSYCHOMOTOR = 'psychomotor';

    protected $fillable = [
        'institute_id',
        'branch_id',
        'session_id',
        'domain_type',
        'name',
        'is_active',
        'display_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(AssessmentItem::class)->orderBy('display_order');
    }
}
