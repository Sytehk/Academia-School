<?php

namespace Modules\Examination\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TranscriptIssue extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'institute_id',
        'branch_id',
        'student_id',
        'verification_code',
        'transcript_number',
        'snapshot',
        'issued_by',
        'issued_at',
    ];

    protected $casts = [
        'snapshot' => 'array',
        'issued_at' => 'datetime',
    ];

    public static function record(int $studentId, array $snapshot): self
    {
        $number = sprintf('TR-%06d', static::withoutGlobalScope('tenant')
            ->where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->count() + 1);

        return static::create([
            'institute_id' => get_institute_id(),
            'branch_id' => get_branch_id(),
            'student_id' => $studentId,
            'verification_code' => strtoupper(Str::random(4)).'-'.strtoupper(Str::random(4)).'-'.strtoupper(Str::random(4)),
            'transcript_number' => $number,
            'snapshot' => $snapshot,
            'issued_by' => getUserId(),
            'issued_at' => now(),
        ]);
    }
}
