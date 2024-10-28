<?php

namespace Modules\LayoutCert\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CertificateIssue extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'institute_id',
        'branch_id',
        'type',
        'certificate_template_id',
        'verification_code',
        'certificate_number',
        'batch_id',
        'student_id',
        'subject_name',
        'snapshot',
        'issued_by',
        'issued_at',
    ];

    protected $casts = [
        'snapshot' => 'array',
        'issued_at' => 'datetime',
    ];

    public static function record(string $type, ?int $studentId, string $subjectName, array $snapshot, array $extra = []): self
    {
        return static::create([
            'institute_id' => get_institute_id(),
            'branch_id' => get_branch_id(),
            'type' => $type,
            'verification_code' => strtoupper(Str::random(4)).'-'.strtoupper(Str::random(4)).'-'.strtoupper(Str::random(4)),
            'certificate_number' => static::nextNumber($type),
            'student_id' => $studentId,
            'subject_name' => $subjectName,
            'snapshot' => $snapshot,
            'issued_by' => getUserId(),
            'issued_at' => now(),
        ] + $extra);
    }

    /**
     * Sequential per institute + type, e.g. CERT-GENERAL-CERTIFICATE-000123. Counts
     * this institute's own issues of the type — safe under BelongsToTenant's scope.
     */
    public static function nextNumber(string $type): string
    {
        $count = static::withoutGlobalScope('tenant')
            ->where('institute_id', get_institute_id())
            ->where('branch_id', get_branch_id())
            ->where('type', $type)
            ->count();

        $prefix = 'CERT-'.strtoupper(str_replace('-', '', substr($type, 0, 12)));

        return sprintf('%s-%06d', $prefix, $count + 1);
    }
}
