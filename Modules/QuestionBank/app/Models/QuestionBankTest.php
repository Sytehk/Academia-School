<?php

namespace Modules\QuestionBank\Models;

use App\Traits\SharedWithInstitute;
use Illuminate\Database\Eloquent\Model;

class QuestionBankTest extends Model
{
    use SharedWithInstitute;

    protected $fillable = [
        'test_name',
        'test_id',
        'image',
        'duration',
        'start_date',
        'end_date',
        'status',
        'created_by',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public const TABLE_NAME = 'question_bank_tests';

    protected $table = self::TABLE_NAME;
}
