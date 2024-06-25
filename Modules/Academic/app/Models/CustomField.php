<?php

namespace Modules\Academic\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomField extends Model
{
    use BelongsToTenant;
    use SoftDeletes;

    protected $fillable = [
        'institute_id',
        'branch_id',
        'module',
        'field_name',
        'label',
        'field_type',
        'options',
        'is_required',
        'show_in_form',
        'show_in_list',
        'show_in_profile',
        'serial',
        'status',
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
        'show_in_form' => 'boolean',
        'show_in_list' => 'boolean',
        'show_in_profile' => 'boolean',
    ];

    public const TABLE_NAME = 'custom_fields';

    protected $table = self::TABLE_NAME;

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function values()
    {
        return $this->hasMany(CustomFieldValue::class, 'custom_field_id');
    }
}
