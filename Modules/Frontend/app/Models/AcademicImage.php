<?php

namespace Modules\Frontend\Models;

use App\Traits\BelongsToInstitute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicImage extends Model
{
    use BelongsToInstitute;
    use SoftDeletes;

    protected $table = 'academic_images';

    public const TABLE_NAME = 'academic_images';

    protected $fillable = [
        'institute_id',
        'title',
        'heading',
        'description',
        'image',
        'status',
        'created_by',
        'created_at',
        'updated_at',
    ];
}
