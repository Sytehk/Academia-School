<?php

namespace Modules\QuestionBank\Models;

use App\Traits\SharedWithInstitute;
use Illuminate\Database\Eloquent\Model;

class QuestionBankYear extends Model
{
    use SharedWithInstitute;

    protected $fillable = [
        'name',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public const TABLE_NAME = 'question_bank_years';

    protected $table = self::TABLE_NAME;
}
