<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Academic\Models\StudentAttendanceLog;

/**
 * Detects IN / OUT for raw biometric punches and persists them to
 * `student_attendance_logs`, without touching the existing `student_attendances`
 * daily register.
 *
 * Detection rule (the school's attendance configuration):
 *  - Punches before the configured `attendance_out_after` setting (default 12:00)
 *    are treated as IN (student entering school).
 *  - Punches at / after that threshold are treated as OUT (student leaving school).
 *
 * The whole flow runs in the unauthenticated device-sync context, so every tenant
 * lookup (settings, school name) is done explicitly by institute/branch id rather
 * than through the auth-based get_institute_id()/get_option() helpers.
 */
class AttendanceInOutService
{
    public const EVENT_IN = 'attendance_in';

    public const EVENT_OUT = 'attendance_out';

    public const REFERENCE_TYPE = 'attendance_log';

    /** Default OUT threshold (H:i) used when the school has not configured one. */
    public const DEFAULT_OUT_AFTER = '12:00';

    /** @var array<string, string> resolved OUT threshold (H:i), keyed by "instituteId|branchId" */
    private array $thresholdCache = [];

    /** @var array<string, string> resolved school name, keyed by "instituteId|branchId" */
    private array $schoolNameCache = [];

    /**
     * Determine whether a punch at $time is an IN or an OUT for the given tenant.
     */
    public function detectType(int $instituteId, int $branchId, string $time): string
    {
        $threshold = $this->outThreshold($instituteId, $branchId);

        return strcmp(substr($time, 0, 5), $threshold) < 0
            ? StudentAttendanceLog::TYPE_IN
            : StudentAttendanceLog::TYPE_OUT;
    }

    /**
     * Persist a batch of punches and return the list of notifications to send.
     *
     * Each element of $punches must contain:
     *  institute_id, branch_id, student_id, class_id, section_id,
     *  date (Y-m-d), time (H:i[:s]), qr_code, phone, student_name
     *
     * Only the first IN and the first OUT of each student per day produce a
     * notification, so repeated punches (e.g. a student tapping out twice) never
     * spam the parent. Duplicate punches are skipped via the unique index.
     *
     * @return array<int, array<string, mixed>> notification payloads
     */
    public function processBatch(array $punches): array
    {
        if (empty($punches)) {
            return [];
        }

        // Preload the punch types already recorded today for the involved students,
        // so first-of-type detection stays O(1) per punch instead of N queries.
        $studentIds = array_values(array_unique(array_column($punches, 'student_id')));
        $dates = array_values(array_unique(array_column($punches, 'date')));

        $existingRows = StudentAttendanceLog::query()
            ->whereIn('student_id', $studentIds)
            ->whereIn('attendance_date', $dates)
            ->get(['student_id', 'attendance_date', 'attendance_time', 'type']);

        $existing = $existingRows
            ->groupBy(fn ($row) => $row->student_id.'|'.$row->attendance_date->toDateString())
            ->map(fn ($group) => $group->pluck('type')->all())
            ->all();

        // Exact-punch lookup (student_id|date|time|type), matching the table's
        // own unique index. A device re-syncing the same window sends the SAME
        // punches again on every run; every one of them used to cost a SELECT
        // (via firstOrCreate()) that just re-confirmed "yes, already here."
        // With this set built from the query above, an already-known duplicate
        // is skipped with zero additional queries, and a genuinely new punch
        // goes straight to create() -- one INSERT instead of firstOrCreate's
        // SELECT-then-INSERT.
        $existingExact = [];
        foreach ($existingRows as $row) {
            $existingExact[$row->student_id.'|'.$row->attendance_date->toDateString().'|'.$row->attendance_time.'|'.$row->type] = true;
        }

        $notifications = [];

        foreach ($punches as $punch) {
            $instituteId = (int) $punch['institute_id'];
            $branchId = (int) $punch['branch_id'];
            $studentId = (int) $punch['student_id'];
            $date = $punch['date'];
            $time = strlen($punch['time']) === 5 ? $punch['time'].':00' : $punch['time'];

            $type = $this->detectType($instituteId, $branchId, $time);
            $key = $studentId.'|'.$date;
            $alreadyTypes = $existing[$key] ?? [];
            $exactKey = $studentId.'|'.$date.'|'.$time.'|'.$type;

            // First punch of this type for the student today (before we record it).
            $isFirstOfType = ! in_array($type, $alreadyTypes, true);

            // Already known to exist -- a re-sync of a punch already recorded
            // (the common case: a device re-sends its whole day's window on
            // every sync). firstOrCreate() would have run a SELECT here only to
            // confirm what this in-memory set already knows; skip it entirely.
            // wasRecentlyCreated would have been false for this row regardless,
            // so no notification would have followed either way.
            if (isset($existingExact[$exactKey])) {
                continue;
            }

            try {
                // A plain create() (INSERT only) instead of firstOrCreate()
                // (SELECT, then INSERT if not found): $existingExact has already
                // ruled out the "it's already there" case for every punch except
                // a genuine race against a concurrent sync, which the unique
                // index below still catches.
                $log = StudentAttendanceLog::create([
                    'student_id' => $studentId,
                    'attendance_date' => $date,
                    'attendance_time' => $time,
                    'type' => $type,
                    'institute_id' => $instituteId,
                    'branch_id' => $branchId,
                    'class_id' => $punch['class_id'] ?? null,
                    'section_id' => $punch['section_id'] ?? null,
                    'qr_code' => $punch['qr_code'] ?? null,
                ]);
            } catch (\Throwable $e) {
                // A duplicate punch (unique index race) or any storage issue
                // must never break the attendance sync — just skip this punch.
                Log::warning('StudentAttendanceLog punch skipped: '.$e->getMessage(), ['student_id' => $studentId]);

                continue;
            }

            // Track both the type and the exact tuple so later punches in the
            // same batch see this one (a duplicate within a single sync payload
            // must not attempt a second INSERT against the same unique key).
            $existingExact[$exactKey] = true;
            if (! in_array($type, $alreadyTypes, true)) {
                $alreadyTypes[] = $type;
                $existing[$key] = $alreadyTypes;
            }

            // Notify only on the first punch of this type for the day. create()
            // always succeeds as a genuinely new row here (the $existingExact
            // check above already excluded pre-existing ones), so there is no
            // wasRecentlyCreated to re-check.
            if (! $isFirstOfType) {
                continue;
            }

            $notifications[] = [
                'log_id' => $log->id,
                'institute_id' => $instituteId,
                'branch_id' => $branchId,
                'student_id' => $studentId,
                'type' => $type,
                'event' => $type === StudentAttendanceLog::TYPE_IN ? self::EVENT_IN : self::EVENT_OUT,
                'phone' => $punch['phone'] ?? null,
                'parameters' => [
                    'student_name' => $punch['student_name'] ?? '',
                    'school_name' => $this->schoolName($instituteId, $branchId),
                    'time' => Carbon::parse($time)->format('h:i A'),
                    'date' => Carbon::parse($date)->format('d M Y'),
                    'class' => $punch['class_name'] ?? '',
                    'section' => $punch['section_name'] ?? '',
                    'roll' => $punch['roll'] ?? '',
                ],
            ];
        }

        return $notifications;
    }

    /**
     * The configured OUT threshold (H:i) for a tenant, falling back to the default.
     */
    public function outThreshold(int $instituteId, int $branchId): string
    {
        // Key the cache by institute AND branch: settingValue() resolves the
        // branch-specific row first, so two branches of the same institute can
        // legitimately have different thresholds. Keying by institute alone made
        // the second branch reuse the first branch's value.
        $cacheKey = $instituteId.'|'.$branchId;
        if (isset($this->thresholdCache[$cacheKey])) {
            return $this->thresholdCache[$cacheKey];
        }

        $value = $this->settingValue($instituteId, $branchId, 'attendance_out_after');

        $threshold = self::DEFAULT_OUT_AFTER;
        if (is_string($value) && preg_match('/^([01]?\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/', trim($value))) {
            $threshold = substr(trim($value), 0, 5);
        }

        return $this->thresholdCache[$cacheKey] = $threshold;
    }

    /**
     * The school's display name for a tenant (used in the WhatsApp message).
     */
    public function schoolName(int $instituteId, int $branchId): string
    {
        // Branch-aware cache key, same reasoning as outThreshold().
        $cacheKey = $instituteId.'|'.$branchId;
        if (isset($this->schoolNameCache[$cacheKey])) {
            return $this->schoolNameCache[$cacheKey];
        }

        $name = $this->settingValue($instituteId, $branchId, 'school_name');

        return $this->schoolNameCache[$cacheKey] = ($name !== null && $name !== '') ? $name : 'School';
    }

    /**
     * Tenant-explicit settings reader. Mirrors get_option() but works without an
     * authenticated user (the device sync endpoint is public).
     */
    private function settingValue(int $instituteId, int $branchId, string $name): ?string
    {
        try {
            // These options (school_name, attendance_out_after) are institute-level.
            // The settings screen saves them without a branch in the lookup, so the
            // row may carry any branch_id or NULL — prefer the student's own branch,
            // then a NULL (shared) row, then any row for the institute.
            $row = DB::table('settings')
                ->where('institute_id', $instituteId)
                ->where('name', $name)
                ->orderByRaw('CASE WHEN branch_id = ? THEN 0 WHEN branch_id IS NULL THEN 1 ELSE 2 END', [$branchId])
                ->first();

            return $row ? (string) $row->value : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
