<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Modules\Academic\Models\StudentAttendance;
use Modules\Academic\Models\StudentSession;

/**
 * Shared QR-scan-to-attendance logic, extracted so new scan entry points
 * (Teacher portal) don't duplicate the Admin implementation
 * (Modules\Academic\Http\Controllers\{WEB\QrAttendanceController,
 * API\APIAttendanceController}::studentQrCodeAttendance) a third time. The
 * existing Admin controllers are intentionally left as-is — this is new,
 * additive code, not a refactor of them.
 *
 * The QR code itself is not a secret (it's printed on the physical ID card —
 * see EnsureAttendanceDevice's doc comment), so the security boundary here is
 * institute/branch scoping (via AcademicContextService::findStudentSessionByQrCode,
 * already tenant-scoped) plus, when a caller is taking attendance for a
 * specific class/section (the Teacher flow), refusing to silently attribute a
 * scan to a different class/section than the one currently open.
 *
 * Check-in/check-out state machine (layered onto the SAME attendance row —
 * no separate table): the first valid scan for the day creates the row (or
 * fills check_in_at on one that already exists, e.g. from manual entry) and
 * reports "checked in"; the next valid scan fills check_out_at and reports
 * "checked out"; every scan after that changes nothing and reports "already
 * checked out". Historical/manual rows that never go through a QR scan keep
 * both columns null forever — nothing here ever backfills them.
 */
class QrAttendanceService
{
    public const STATUS_NOT_FOUND = 'not_found';

    public const STATUS_INACTIVE = 'inactive';

    public const STATUS_SECTION_MISMATCH = 'section_mismatch';

    public const STATUS_CHECKED_IN = 'checked_in';

    public const STATUS_CHECKED_OUT = 'checked_out';

    public const STATUS_ALREADY_CHECKED_OUT = 'already_checked_out';

    public function __construct(private readonly AcademicContextService $academicContext) {}

    /**
     * @param  int|null  $expectedClassId  When the caller is taking attendance for a specific
     *                                     class/section (Teacher flow), the scanned student must
     *                                     belong to it — otherwise STATUS_SECTION_MISMATCH.
     */
    public function scan(string $qrCode, ?int $expectedClassId = null, ?int $expectedSectionId = null): array
    {
        $studentSession = $this->academicContext->findStudentSessionByQrCode($qrCode);

        if (! $studentSession) {
            return $this->result(self::STATUS_NOT_FOUND, null, 'Student Session not found.');
        }

        if ($expectedClassId !== null && (int) $studentSession->class_id !== $expectedClassId) {
            return $this->result(self::STATUS_SECTION_MISMATCH, $studentSession, 'This student does not belong to the selected class.');
        }

        if ($expectedSectionId !== null && (int) $studentSession->section_id !== $expectedSectionId) {
            return $this->result(self::STATUS_SECTION_MISMATCH, $studentSession, 'This student does not belong to the selected section.');
        }

        $student = $studentSession->student;
        if (! $student || (int) $student->status !== 1) {
            return $this->result(self::STATUS_INACTIVE, $studentSession, 'This student is not active.');
        }

        $result = $this->transitionAttendance(
            (int) get_institute_id(),
            (int) get_branch_id(),
            (int) $studentSession->student_id,
            (int) $studentSession->class_id,
            (int) $studentSession->section_id,
            Carbon::today()->toDateString(),
            Carbon::now(),
        );

        return $this->result($result['status'], $studentSession, $result['message'], $result['attendance']);
    }

    /**
     * Device/physical QR punch — the SAME row-level state machine as scan(),
     * reused rather than duplicated (App\Services\QrAttendanceService is the
     * one place this logic lives). Takes an already-resolved student/class/
     * section (the caller — the device sync endpoint — has already scoped the
     * QR-code lookup to the device's own institute/branch) and the device's
     * own punch timestamp rather than the live request time, since a device
     * sync delivers a batch of already-happened punches, not a live scan.
     *
     * A device resyncing its whole day's window on every call is normal
     * behaviour (see AttendanceInOutService), so this never advances the
     * state machine on a punch at or before the already-recorded check-in —
     * that is the SAME event resent, not a second (checkout) one. Once both
     * timestamps are set, every further punch is already_checked_out
     * regardless of its own timestamp, exactly like a third interactive scan.
     */
    public function recordPunch(
        int $instituteId,
        int $branchId,
        int $studentId,
        int $classId,
        int $sectionId,
        Carbon $punchAt,
    ): array {
        return $this->transitionAttendance(
            $instituteId,
            $branchId,
            $studentId,
            $classId,
            $sectionId,
            $punchAt->toDateString(),
            $punchAt,
            treatNonAdvancingAsNoOp: true,
        );
    }

    /**
     * lockForUpdate() inside the transaction is the actual guarantee against
     * two simultaneous scans/punches of the same student producing duplicate
     * rows or conflicting check-in/out times — the exists()-style pre-checks
     * used elsewhere in this codebase are a race-prone convenience, not the
     * real protection. If two scans arrive at once, the second blocks until
     * the first's transaction commits, then sees the row exactly as the
     * first left it and acts on that — never on stale data.
     *
     * $treatNonAdvancingAsNoOp is false for every interactive caller
     * (scan()'s existing, unchanged behaviour: each call unconditionally
     * advances the state machine) and true only for recordPunch(), where a
     * resent punch must not be mistaken for a genuinely new second event.
     */
    private function transitionAttendance(
        int $instituteId,
        int $branchId,
        int $studentId,
        int $classId,
        int $sectionId,
        string $date,
        Carbon $eventAt,
        bool $treatNonAdvancingAsNoOp = false,
    ): array {
        $query = fn () => StudentAttendance::where('student_id', $studentId)
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->where('period_id', 1)
            ->whereDate('date', $date);

        return DB::transaction(function () use ($query, $instituteId, $branchId, $studentId, $classId, $sectionId, $date, $eventAt, $treatNonAdvancingAsNoOp) {
            $attendance = $query()->lockForUpdate()->first();

            if (! $attendance) {
                try {
                    $attendance = StudentAttendance::create([
                        'institute_id' => $instituteId,
                        'branch_id' => $branchId,
                        'student_id' => $studentId,
                        'class_id' => $classId,
                        'section_id' => $sectionId,
                        'period_id' => 1,
                        'date' => $date,
                        'attendance' => 1,
                        'check_in_at' => $eventAt,
                    ]);

                    return ['status' => self::STATUS_CHECKED_IN, 'message' => 'Checked in successfully.', 'attendance' => $attendance];
                } catch (QueryException $e) {
                    // Two simultaneous first-scans both saw "no row" and raced to
                    // insert; the DB's own unique index (student_unique_attendance)
                    // is the real guarantee. MySQL error 1062 is specifically
                    // "Duplicate entry" — the broader SQLSTATE 23000 class also
                    // covers FK/NOT-NULL violations, which must not be swallowed.
                    if ((int) ($e->errorInfo[1] ?? 0) !== 1062) {
                        throw $e;
                    }

                    // The other transaction's insert has committed by the time our
                    // insert fails on the unique index — lock and re-read it fresh,
                    // then fall through to the update logic below exactly as if we
                    // had found it on the first read.
                    $attendance = $query()->lockForUpdate()->first();
                }
            }

            if (is_null($attendance->check_in_at)) {
                $attendance->check_in_at = $eventAt;
                $attendance->save();

                return ['status' => self::STATUS_CHECKED_IN, 'message' => 'Checked in successfully.', 'attendance' => $attendance];
            }

            // A device resending the same window means the SAME punch that set
            // check_in_at arrives again on every later sync; without this guard
            // it would be misread as a distinct second (checkout) event. Never
            // relevant for interactive scans ($treatNonAdvancingAsNoOp is false
            // there), since Carbon::now() only ever moves forward between calls.
            if ($treatNonAdvancingAsNoOp && $eventAt->lte($attendance->check_in_at)) {
                return ['status' => self::STATUS_CHECKED_IN, 'message' => 'Already checked in.', 'attendance' => $attendance];
            }

            if (is_null($attendance->check_out_at)) {
                $attendance->check_out_at = $eventAt;
                $attendance->save();

                return ['status' => self::STATUS_CHECKED_OUT, 'message' => 'Checked out successfully.', 'attendance' => $attendance];
            }

            return ['status' => self::STATUS_ALREADY_CHECKED_OUT, 'message' => 'This student has already checked out today.', 'attendance' => $attendance];
        });
    }

    private function result(string $status, ?StudentSession $studentSession, string $message, ?StudentAttendance $attendance = null): array
    {
        return [
            'status' => $status,
            'message' => $message,
            'student_session' => $studentSession,
            'attendance' => $attendance,
            'check_in_at' => $attendance?->check_in_at?->toDateTimeString(),
            'check_out_at' => $attendance?->check_out_at?->toDateTimeString(),
        ];
    }
}
