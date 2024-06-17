<?php

namespace App\Services;

use App\Models\AdmissionNumberSequence;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Modules\Academic\Models\Student;
use Modules\Authentication\Models\Institute;

/**
 * Institute-wise admission number generation.
 *
 * Manual mode leaves registration_no exactly as the caller supplied it
 * (existing behaviour, unchanged). Auto mode ignores whatever the caller
 * sent and mints the institute's next number — sequential per institute,
 * starting at 000001, formatted <prefix><000001>.
 *
 * Concurrency: the sequence row is locked with lockForUpdate() inside a
 * transaction, so two students created at the same instant can never be
 * handed the same number. As a second line of defence (a pre-existing manual
 * number might already occupy a slot the sequence is about to hand out),
 * candidates are checked against the students table and skipped forward —
 * this also means switching an institute to auto mode later never collides
 * with admission numbers issued manually before the switch.
 */
class AdmissionNumberService
{
    private const PAD_LENGTH = 6;

    private const MAX_ATTEMPTS = 1000;

    public function isAuto(Institute $institute): bool
    {
        return $institute->admission_no_mode === 'auto';
    }

    /**
     * The admission number to store for a new student.
     *
     * @return string|null the generated number in auto mode; $manualValue unchanged in manual mode
     */
    public function resolve(Institute $institute, ?string $manualValue): ?string
    {
        return $this->isAuto($institute) ? $this->next($institute) : $manualValue;
    }

    public function next(Institute $institute): string
    {
        return DB::transaction(function () use ($institute) {
            $sequence = AdmissionNumberSequence::where('institute_id', $institute->id)
                ->lockForUpdate()
                ->first();

            if (! $sequence) {
                // firstOrCreate isn't atomic under a concurrent race on a brand-new
                // institute; a duplicate insert is caught and re-read under the lock.
                try {
                    $sequence = AdmissionNumberSequence::create(['institute_id' => $institute->id, 'next_number' => 1]);
                } catch (QueryException) {
                    $sequence = AdmissionNumberSequence::where('institute_id', $institute->id)->lockForUpdate()->first();
                }
            }

            $prefix = (string) ($institute->admission_no_prefix ?? '');
            $candidate = $sequence->next_number;

            for ($i = 0; $i < self::MAX_ATTEMPTS; $i++) {
                $number = $prefix.str_pad((string) $candidate, self::PAD_LENGTH, '0', STR_PAD_LEFT);

                $taken = Student::withoutGlobalScopes()
                    ->where('institute_id', $institute->id)
                    ->where('registration_no', $number)
                    ->exists();

                if (! $taken) {
                    $sequence->update(['next_number' => $candidate + 1]);

                    return $number;
                }

                $candidate++;
            }

            throw new \RuntimeException('Could not generate a free admission number after '.self::MAX_ATTEMPTS.' attempts.');
        });
    }
}
