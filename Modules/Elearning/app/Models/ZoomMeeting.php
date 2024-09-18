<?php

namespace Modules\Elearning\Models;

use App\Traits\BelongsToInstitute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZoomMeeting extends Model
{
    use BelongsToInstitute;
    use HasFactory;

    public const TABLE_NAME = 'lms_zoom_meetings';

    protected $table = self::TABLE_NAME;

    protected $fillable = [
        'institute_id',
        'course_id',
        'branch_id',
        'meeting_id',
        'topic',
        'agenda',
        'start_time',
        'duration',
        'password',
        'join_url',
        'start_url',
        'created_by',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
