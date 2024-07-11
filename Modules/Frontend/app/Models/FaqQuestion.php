<?php

namespace Modules\Frontend\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FaqQuestion extends Model
{
    use BelongsToTenant;
    use HasFactory, SoftDeletes;

    protected $table = 'faq_questions';

    public const TABLE_NAME = 'faq_questions';

    protected $fillable = [
        'institute_id',
        'branch_id',
        'question',
        'answer',
        'status',
        'created_by',
        'created_at',
        'updated_at',
    ];
}
