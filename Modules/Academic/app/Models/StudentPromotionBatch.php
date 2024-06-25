<?php

namespace Modules\Academic\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** One promotion run: a class (or section) moved from one academic session to the next. */
class StudentPromotionBatch extends Model
{
    use BelongsToTenant;

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_REVERTED = 'reverted';

    protected $fillable = [
        'institute_id',
        'branch_id',
        'from_session_id',
        'to_session_id',
        'from_class_id',
        'from_section_id',
        'rules',
        'summary',
        'status',
        'note',
        'created_by',
        'reverted_by',
        'reverted_at',
    ];

    protected $casts = [
        'rules' => 'array',
        'summary' => 'array',
        'reverted_at' => 'datetime',
    ];

    public function promotions(): HasMany
    {
        return $this->hasMany(StudentPromotion::class, 'batch_id');
    }
}
