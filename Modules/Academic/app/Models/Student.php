<?php

namespace Modules\Academic\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Academic\Database\Factories\StudentFactory;
use Modules\Authentication\Models\Institute;
use Modules\Authentication\Models\User;
use Modules\Finance\Models\StudentCollection;
use Modules\ParentModule\Models\ParentModel;

class Student extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $table = 'students';

    protected $fillable = [
        'institute_id',
        'branch_id',
        'user_id',
        'parent_id',
        'first_name',
        'last_name',
        'phone',
        'registration_no',
        'roll_no', // legacy — session-authoritative field is student_sessions.roll
        'group', // legacy — session-authoritative field is student_sessions.group_id
        'father_name',
        'mother_name',
        'birthday',
        'gender',
        'blood_group',
        'religion',
        'address',
        'student_category_id',
        'status',
        'graduated_at',
        'graduated_session_id',
        'information_sent_to_name',
        'information_sent_to_relation',
        'information_sent_to_phone',
        'information_sent_to_address',
        'state',
        'country',
        'access_key',
        'activities',
        'remarks',
        'nationality',
        'birth_certificate_no',
        'nid_no',
        'ethnic',
        'date_of_admission',
        'application_number',
        'admission_place',
        'tc_date',
    ];

    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id')->select('id', 'name', 'email', 'phone', 'image', 'status');
    }

    public function studentSession(): BelongsTo
    {
        return $this->belongsTo(StudentSession::class, 'id', 'student_id')->with('class', 'section', 'session');
    }

    public function studentCategory(): BelongsTo
    {
        return $this->belongsTo(StudentCategory::class, 'student_category_id', 'id');
    }

    public function parentInfo(): HasOne
    {
        return $this->hasOne(ParentModel::class)->with('user');
    }

    /**
     * Legacy — reads students.group. Prefer $student->studentSession->group
     * (StudentSession::group(), keyed off student_sessions.group_id) instead.
     */
    public function studentGroup(): BelongsTo
    {
        return $this->belongsTo(StudentGroup::class, 'group', 'id');
    }

    public function collections(): HasMany
    {
        return $this->hasMany(StudentCollection::class, 'student_id');
    }

    public function studentLibrary(): BelongsTo
    {
        return $this->belongsTo(LibraryMember::class, 'id', 'student_id');
    }

    public function customFieldValues()
    {
        return $this->morphMany(CustomFieldValue::class, 'model');
    }

    /**
     * Per-student device-access policy (device_controls.student_id).
     * The StudentAuthController login flow reads $student->deviceControl and
     * $student->devices; without these relations that flow threw
     * RelationNotFoundException.
     */
    public function deviceControl(): HasOne
    {
        return $this->hasOne(DeviceControl::class, 'student_id');
    }

    public function devices(): HasMany
    {
        return $this->hasMany(UserDevice::class, 'student_id');
    }

    protected static function newFactory()
    {
        return StudentFactory::new();
    }
}
