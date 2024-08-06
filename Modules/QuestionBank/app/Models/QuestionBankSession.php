<?php

namespace Modules\QuestionBank\Models;

use App\Traits\SharedWithInstitute;
use Illuminate\Database\Eloquent\Model;

class QuestionBankSession extends Model
{
    use SharedWithInstitute;

    protected $fillable = [
        'institute_id',
        'name',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public const TABLE_NAME = 'question_bank_sessions';

    protected $table = self::TABLE_NAME;
}
