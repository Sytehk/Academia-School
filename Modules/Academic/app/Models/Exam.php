<?php

namespace Modules\Academic\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use BelongsToTenant;

    protected $table = 'exams';

    protected $fillable = ['institute_id', 'branch_id', 'session_id', 'name', 'exam_code', 'note', 'status'];
}
