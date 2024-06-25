<?php

namespace Modules\Academic\Models;

use App\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Authentication\Models\User;

class GoogleMeetMeeting extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $table = 'google_meet_meetings';

    protected $fillable = [
        'institute_id',
        'branch_id',
        'title',
        'description',
        'teacher_id',
        'class_id',
        'section_id',
        'group_id',
        'subject_id',
        'start_date',
        'start_time',
        'end_time',
        'duration',
        'meet_link',
        'meet_event_id',
        'organizer_id',
        'status',
        'is_recurring',
        'recurrence_frequency',
        'recurrence_end_date',
        'parent_meeting_id',
        'recording_url',
        'attachment_path',
        'notes',
        'visibility',
        'created_by',
    ];

    protected $casts = [
        'visibility' => 'array',
        'is_recurring' => 'boolean',
        'start_date' => 'date',
        'recurrence_end_date' => 'date',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function classModel(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(StudentGroup::class, 'group_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function computedStatus(): string
    {
        if ($this->status === 'cancelled') {
            return 'Cancelled';
        }

        $now = now();
        $start = Carbon::parse($this->start_date->format('Y-m-d').' '.$this->start_time);
        $end = $this->end_time
            ? Carbon::parse($this->start_date->format('Y-m-d').' '.$this->end_time)
            : $start->copy()->addMinutes($this->duration);

        if ($now->lt($start)) {
            return 'Upcoming';
        }

        if ($now->between($start, $end)) {
            return 'Live Now';
        }

        return 'Completed';
    }
}
