<?php

namespace Modules\QuestionBank\Models;

use App\Traits\SharedWithInstitute;
use Illuminate\Database\Eloquent\Model;

class QuestionBankSource extends Model
{
    use SharedWithInstitute;

    protected $fillable = [
        'source_name',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public const TABLE_NAME = 'question_bank_sources';

    protected $table = self::TABLE_NAME;

    public function subSources()
    {
        return $this->hasMany(QuestionBankSubSource::class, 'question_bank_source_id');
    }
}
