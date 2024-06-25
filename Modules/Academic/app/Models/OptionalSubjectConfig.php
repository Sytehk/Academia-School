<?php

namespace Modules\Academic\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OptionalSubjectConfig extends Model
{
    use BelongsToTenant;
    use SoftDeletes;

    protected $fillable = [
        'institute_id',
        'branch_id',
        'class_id',
        'group_id',
        'name',
        'total_mark',
        'selection_limit',
        'is_active',
    ];

    public const TABLE_NAME = 'optional_subject_configs';

    protected $table = self::TABLE_NAME;

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function group()
    {
        return $this->belongsTo(StudentGroup::class, 'group_id');
    }

    public function items()
    {
        return $this->hasMany(OptionalSubjectItem::class);
    }
}
