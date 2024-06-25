<?php

namespace Modules\Academic\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubjectConfig extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'institute_id',
        'branch_id',
        'class_id',
        'group_id',
        'subject_id',
        'subject_type',
        'sr_no',
        'merge_config_id',
    ];

    public function subject()
    {
        return $this->belongsTo(
            Subject::class
        );
    }

    public function class()
    {
        return $this->belongsTo(
            ClassModel::class,
            'class_id'
        );
    }

    public function group()
    {
        return $this->belongsTo(
            StudentGroup::class,
            'group_id'
        );
    }
}
