<?php

namespace Modules\QuestionBank\Models;

use App\Traits\SharedWithInstitute;
use Illuminate\Database\Eloquent\Model;

class QuestionBankBoard extends Model
{
    use SharedWithInstitute;

    protected $fillable = [
        'board',
        'short_name',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public const TABLE_NAME = 'question_bank_boards';

    protected $table = self::TABLE_NAME;
}
