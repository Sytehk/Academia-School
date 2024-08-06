<?php

namespace Modules\QuestionBank\Models;

use App\Traits\BelongsToInstitute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuestionCategory extends Model
{
    use BelongsToInstitute;
    use SoftDeletes;

    public const TABLE_NAME = 'question_categories';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'institute_id',
        'name',
        'priority',
        'slug',
        'image',
        'icon',
        'color_code',
        'status',
        'created_by',
    ];

    public function subjects(): HasMany
    {
        return $this->hasMany(QuestionBankSubject::class, 'question_category_id');
    }
}
