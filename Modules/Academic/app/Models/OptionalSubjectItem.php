<?php

namespace Modules\Academic\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OptionalSubjectItem extends Model
{
    use BelongsToTenant;
    use SoftDeletes;

    protected $fillable = [
        'institute_id',
        'branch_id',
        'optional_subject_config_id',
        'subject_id',
        'mark',
        'priority',
    ];

    public const TABLE_NAME = 'optional_subject_items';

    protected $table = self::TABLE_NAME;

    public function config()
    {

        return $this->belongsTo(
            OptionalSubjectConfig::class,
            'optional_subject_config_id'
        );
    }

    public function subject()
    {

        return $this->belongsTo(
            Subject::class
        );
    }
}
