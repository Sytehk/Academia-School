<?php

namespace Modules\Academic\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ResultCard extends Model
{
    use BelongsToTenant;
    use SoftDeletes;

    protected $fillable = [
        'institute_id',
        'name',
        'qr_code',
        'signature_id',
        'teacher_signature_id',
        'background_image',
        'header_logo',
        'stamp_image',
        'border_design',
        'watermark',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public const TABLE_NAME = 'result_cards';

    protected $table = self::TABLE_NAME;
}
