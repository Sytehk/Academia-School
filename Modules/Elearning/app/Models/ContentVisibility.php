<?php

namespace Modules\Elearning\Models;

use App\Traits\BelongsToInstitute;
use Illuminate\Database\Eloquent\Model;

class ContentVisibility extends Model
{
    use BelongsToInstitute;

    protected $guarded = ['id'];

    public const TABLE_NAME = 'content_visibility';

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
