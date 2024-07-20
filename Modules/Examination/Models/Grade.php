<?php

namespace Modules\Examination\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    use BelongsToTenant;

    protected $table = 'grades';

    protected $fillable =
        [
            'institute_id',
            'branch_id',
            'session_id',
            'grade_name',
            'grade_number',
            'grade_point',
            'grade_range',
            'number_high',
            'number_low',
            'point_high',
            'point_low',
            'priority',
        ];

    protected $casts = [
        'grade_number' => 'decimal:2',
        'grade_point' => 'decimal:2',
        'number_high' => 'decimal:2',
        'number_low' => 'decimal:2',
        'point_high' => 'decimal:2',
        'point_low' => 'decimal:2',
    ];
}
