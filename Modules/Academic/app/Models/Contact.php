<?php

namespace Modules\Academic\Models;

use App\Traits\BelongsToInstitute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use BelongsToInstitute;
    use HasFactory;

    protected $fillable = ['institute_id', 'branch_id', 'name', 'email', 'phone', 'subject', 'message', 'user_id', 'isSeen'];
}
