<?php

namespace Modules\Examination\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Modules\Academic\Models\ClassModel;

class ExamCode extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'institute_id',
        'branch_id',
        'session_id',
        'class_model_id',
        'short_code_id',
        'short_code_title',
        'short_code_note',
        'default_id',
        'total_mark',
        'accept_percent',
        'pass_mark',
    ];

    protected $casts = [
        'total_mark' => 'decimal:2',
        'accept_percent' => 'decimal:2',
        'pass_mark' => 'decimal:2',
    ];

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_model_id', 'id')->select('id', 'class_name');
    }

    public function shortCode()
    {
        return $this->belongsTo(ShortCode::class, 'short_code_id', 'id');
    }
}
