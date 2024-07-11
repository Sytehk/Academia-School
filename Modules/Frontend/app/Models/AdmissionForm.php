<?php

namespace Modules\Frontend\Models;

use App\Traits\BelongsToInstitute;
use Illuminate\Database\Eloquent\Model;

class AdmissionForm extends Model
{
    use BelongsToInstitute;

    protected $fillable = [
        'institute_id',
        'branch_id',
        'class_id',
        'section_id',
        'group_id',
        'first_name',
        'last_name',
        'father_name',
        'mother_name',
        'email',
        'phone',
        'birthday',
        'gender',
        'blood_group',
        'religion',
        'address',
        'registration_no',
        'previous_school',
        'previous_class',
        'guardian_name',
        'guardian_email',
        'guardian_phone',
        'guardian_relation',
        'guardian_address',
        'image',
        'remarks',
        'status',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'birthday' => 'date',
        'approved_at' => 'datetime',
    ];

    public const TABLE_NAME = 'admission_forms';

    protected $table = self::TABLE_NAME;
}
