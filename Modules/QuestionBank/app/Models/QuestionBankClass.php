<?php

namespace Modules\QuestionBank\Models;

use App\Traits\SharedWithInstitute;
use Illuminate\Database\Eloquent\Model;

class QuestionBankClass extends Model
{
    use SharedWithInstitute;

    protected $fillable = [
        'name',
        'status',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public const TABLE_NAME = 'question_bank_classes';

    protected $table = self::TABLE_NAME;

    public function groups()
    {
        return $this->hasMany(QuestionBankGroup::class, 'class_id');
    }

    public function questions()
    {
        return $this->hasMany(Question::class, 'question_bank_class_id');
    }

    public function subjects()
    {
        return $this->hasMany(QuestionBankSubject::class, 'id');
    }
}
