<?php

namespace Modules\Academic\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomFieldValue extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'institute_id',
        'branch_id',
        'custom_field_id',
        'model_type',
        'model_id',
        'value',
        'status',
    ];

    public const TABLE_NAME = 'custom_field_values';

    protected $table = self::TABLE_NAME;

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function field()
    {
        return $this->belongsTo(CustomField::class, 'custom_field_id');
    }

    public function model()
    {
        return $this->morphTo();
    }
}
