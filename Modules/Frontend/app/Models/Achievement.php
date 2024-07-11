<?php

namespace Modules\Frontend\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Achievement extends Model
{
    use BelongsToTenant;
    use SoftDeletes;

    protected $fillable = [
        'institute_id',
        'branch_id',
        'title',
        'description',
        'image',
        'graduates',
        'certified_teachers',
        'student_campus',
        'students',
        'status',
    ];

    public const TABLE_NAME = 'achievements';

    protected $table = self::TABLE_NAME;
}
