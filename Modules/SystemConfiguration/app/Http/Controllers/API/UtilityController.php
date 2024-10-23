<?php

namespace Modules\SystemConfiguration\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureAttendanceDevice;
use App\Services\AttendanceInOutService;
use App\Services\DatabaseBackupService;
use App\Services\QrAttendanceService;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Authentication\Models\User;
use Modules\SystemConfiguration\Http\Requests\Settings\SettingUpdateRequest;
use Modules\SystemConfiguration\Models\Setting;

class UtilityController extends Controller
{
    public function academicYearChange(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|integer',
        ]);

        $data = [];
        $data['value'] = intval($request->session_id);
        $data['updated_at'] = Carbon::now();
        Setting::where('institute_id', get_institute_id())->where('branch_id', get_branch_id())->where('name', 'academic_year')->update($data);

        // Setting::where(...)->update() is a query-builder mass update, which does
        // NOT fire Eloquent's saved/deleted model events — so the Setting::saved()
        // listener that flushes settings_map() (see AppServiceProvider) never runs
        // for this call. In an ordinary PHP-FPM/mod_php request this is invisible
        // (the map starts empty every request), but under a long-running process
        // (queue worker, Octane) get_option('academic_year') would keep serving the
        // pre-change value for the rest of that process's life. Flushed explicitly.
        clear_settings_map();

        return $this->responseSuccess(
            [$data['value']],
            _lang('Session Changed Successfully.')
        );
    }

    public function changeBranch(Request $request): JsonResponse
    {
        $request->validate([
            'branch_id' => 'required|integer',
        ]);

        $branch_id = intval($request->branch_id);
        if ($branch_id != '') {
            User::where('id', auth()->user()->id)->update([
                'branch_id' => $branch_id,
            ]);
        }

        return $this->responseSuccess(
            [$branch_id],
            _lang('Branch Changed Successfully.')
        );
    }

    public function backupDatabase()
    {
        // A raw SQL dump necessarily contains every tenant's data — restrict it to a
        // platform-level SAAS Admin rather than any authenticated institute user.
        abort_unless(optional(auth()->user())->user_type === 'SAAS Admin', 403, 'Only a SAAS Admin can export a database backup.');

        @ini_set('max_execution_time', 0);
        @set_time_limit(0);

        $return = '';
        $database = 'Tables_in_'.DB::getDatabaseName();
        $tables = [];
        $result = DB::select('SHOW TABLES');

        foreach ($result as $table) {
            $tables[] = $table->$database;
        }

        // loop through the tables
        foreach ($tables as $table) {
            $return .= "DROP TABLE IF EXISTS $table;";
            $result2 = DB::select("SHOW CREATE TABLE $table");
            $row2 = $result2[0]->{'Create Table'};
            $return .= "\n\n".$row2.";\n\n";
            $result = DB::select("SELECT * FROM $table");

            foreach ($result as $row) {
                $return .= "INSERT INTO $table VALUES(";
                foreach ($row as $key => $val) {
                    $return .= "'".addslashes($val)."',";
                }
                $return = substr_replace($return, '', -1);
                $return .= ");\n";
            }

            $return .= "\n\n\n";
        }

        // Save outside the public webroot so the dump can never be fetched directly by URL.
        $directory = storage_path('app/backups');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        $file = $directory.DIRECTORY_SEPARATOR.'DB-BACKUP-'.now()->format('Y-m-d_His').'-'.Str::random(12).'.sql';
        file_put_contents($file, $return);

        return response()->download($file)->deleteFileAfterSend(true);
    }

    /** Manual or scheduled backup to the configured destination (local disk or S3/R2). */
    public function runBackup(DatabaseBackupService $backups): JsonResponse
    {
        abort_unless(optional(auth()->user())->user_type === 'SAAS Admin', 403, 'Only a SAAS Admin can run a backup.');

        try {
            $result = $backups->run();

            return $this->responseSuccess($result, _lang('Database backup completed successfully.'));
        } catch (Exception $e) {
            return $this->responseError([], _lang('Backup failed').': '.$e->getMessage());
        }
    }

    /** Backups currently stored on the configured remote (S3/R2) destination. */
    public function listBackups(DatabaseBackupService $backups): JsonResponse
    {
        abort_unless(optional(auth()->user())->user_type === 'SAAS Admin', 403, 'Only a SAAS Admin can view backups.');

        return $this->responseSuccess([
            'remote_configured' => $backups->isRemoteConfigured(),
            'backups' => $backups->listRemote(),
        ], _lang('Backups fetched successfully.'));
    }

    public function settings(SettingUpdateRequest $request): JsonResponse
    {
        try {
            if ($request->isMethod('get')) {
                $settings = Setting::where('institute_id', get_institute_id())
                    // ->where('branch_id', get_branch_id())
                    ->pluck('value', 'name');

                return $this->responseSuccess($settings, _lang('System Settings fetched successfully.'));
            }

            // If POST request, update or create settings
            DB::beginTransaction();
            foreach ($request->validated() as $key => $value) {
                if ($key === '_token') {
                    continue;
                }

                Setting::updateOrInsert(
                    [
                        'institute_id' => get_institute_id(),
                        // 'branch_id' => get_branch_id(),
                        'name' => $key,
                    ],
                    [
                        'value' => $value,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
            DB::commit();

            return $this->responseSuccess([], _lang('System Settings have been successfully updated.'));
        } catch (Exception $e) {
            DB::rollBack();

            return $this->responseError(['error' => $e->getMessage()], _lang('Failed to store records unsuccessfully.'));
        }
    }

    public function dropDatabase(Request $request)
    {
        if (! app()->isLocal()) {
            return response()->json(['error' => 'Operation not allowed in this environment.'], 403);
        }

        $configuredToken = env('DB_DROP_TOKEN');
        $suppliedToken = (string) $request->input('token', '');

        // Require a non-empty, explicitly configured token — an unset DB_DROP_TOKEN must
        // never make this endpoint pass with an empty/missing "token" parameter.
        if (empty($configuredToken) || ! hash_equals((string) $configuredToken, $suppliedToken)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if (optional(auth()->user())->user_type !== 'SAAS Admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $dbName = DB::getDatabaseName();
            DB::statement("DROP DATABASE `$dbName`");
            DB::statement("CREATE DATABASE `$dbName`");

            return response()->json(['message' => "Database `$dbName` dropped successfully."]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function uploadLogo(Request $request): JsonResponse
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg|max:8192',
        ]);

        try {
            $instituteId = get_institute_id();
            $branchId = get_branch_id();

            $imageUrl = null;
            if (isset($request['logo'])) {
                if (isset($request['logo']) && $request['logo']->isValid()) {
                    $imageUrl = fileUploader('logos/', 'png', $request['logo']);
                }
            }

            // Prepare data
            $data = [
                'institute_id' => $instituteId,
                'branch_id' => $branchId,
                'name' => 'logo',
                'value' => $imageUrl,
                'updated_at' => Carbon::now(),
            ];

            // Update or create logo setting
            $setting = Setting::updateOrCreate(
                [
                    'institute_id' => $instituteId,
                    'branch_id' => $branchId,
                    'name' => 'logo',
                ],
                $data
            );

            return $this->responseSuccess(
                $setting,
                _lang('System logo has been successfully uploaded.')
            );
        } catch (Exception $e) {
            return $this->responseError([], _lang('Logo upload failed: ').$e->getMessage());
        }
    }

    public function getAllImageFolderUrls(): JsonResponse
    {
        $base_url = url('/public/uploads');

        // Define dynamic folders
        $folders = [
            'backup',
            'files',
            'files/assignments',
            'files/syllabus',
            'images',
            'images/librarians',
            'images/staffs',
            'images/students',
            'images/teachers',
            'images/users',
            'signatures',
        ];

        $data = [];
        // Generate dynamic folder URLs
        foreach ($folders as $folder) {
            $key = str_replace(['/', '\\'], '_', $folder).'_url';
            $data[$key] = str_replace('\\', '/', $base_url.'/'.$folder.'/');
        }

        // Add specific static URLs
        $staticUrls = [
            'system_logo' => url('/public/uploads/logos/'),
        ];

        // Merge static URLs with dynamic URLs
        $data = array_merge($data, $staticUrls);

        return response()->json([
            'status' => 'success',
            'message' => 'All image folder URLs fetched successfully.',
            'data' => $data,
        ]);
    }

    public function studentDeviceAttendance(Request $request): JsonResponse
    {
        $logs = $request->input('logs', []);

        if (empty($logs)) {
            return response()->json([
                'status' => false,
                'message' => 'No attendance logs provided.',
            ], 400);
        }

        // ✅ Step 1: Collect all QR codes
        $qrCodes = collect($logs)
            ->pluck('id')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        // ✅ Step 2: Fetch all student-session data in ONE query
        // (parent phone + student name/phone are only used for the IN/OUT WhatsApp
        // notification; the left join never filters out a student.)
        //
        // Scoped to the institute that owns the device token EnsureAttendanceDevice
        // just validated. Without this the lookup matched qr_code across every
        // institute on the platform, so a QR code lifted from a printed ID card
        // could write attendance into whichever tenant happened to hold it.
        $device = $request->attributes->get(EnsureAttendanceDevice::CONTEXT);

        $students = DB::table('student_sessions as ss')
            ->join('students as s', 's.id', '=', 'ss.student_id')
            ->leftJoin('parent_models as pm', function ($join) {
                $join->on('pm.student_id', '=', 's.id')->whereNull('pm.deleted_at');
            })
            ->when($device, function ($query) use ($device) {
                $query->where('ss.institute_id', $device['institute_id']);

                if ($device['branch_id'] !== null) {
                    $query->where('ss.branch_id', $device['branch_id']);
                }
            })
            ->whereIn('ss.qr_code', $qrCodes)
            ->select(
                'ss.qr_code',
                'ss.class_id',
                'ss.section_id',
                'ss.roll',
                's.id as student_id',
                's.institute_id',
                's.branch_id',
                's.status',
                's.first_name',
                's.last_name',
                's.phone',
                's.information_sent_to_phone',
                'pm.parent_phone'
            )
            ->get()
            ->keyBy('qr_code'); // 🔥 key by QR for fast lookup

        // ✅ Step 3: Resolve each log to a student + a valid punch instant (NO DB
        // query inside this loop). Every punch is routed through the SAME
        // check-in/check-out state machine QrAttendanceService::scan() uses
        // (App\Services\QrAttendanceService::recordPunch()) — this used to be an
        // independent upsert() that only ever set `attendance`, never
        // check_in_at/check_out_at. Grouped by student and applied in
        // chronological order per student, since one sync batch can carry both
        // a student's IN and OUT punch together.
        $punchesByStudent = [];
        foreach ($logs as $log) {
            $qrCode = $log['id'] ?? null;
            $checkTime = $log['timestamp'] ?? null;

            if (! $qrCode || ! $checkTime || ! isset($students[$qrCode])) {
                continue;
            }

            $student = $students[$qrCode];

            // Same rejection scan() applies before ever touching student_attendances.
            if ((int) ($student->status ?? 1) !== 1) {
                continue;
            }

            // A device's payload is external input; a malformed timestamp must
            // never corrupt attendance data or 500 the rest of a genuine batch.
            try {
                $punchAt = Carbon::parse($checkTime);
            } catch (\Throwable $e) {
                continue;
            }

            $punchesByStudent[$student->student_id][] = [
                'qr_code' => $qrCode,
                'student' => $student,
                'punch_at' => $punchAt,
            ];
        }

        if (empty($punchesByStudent)) {
            return response()->json([
                'status' => true,
                'message' => 'No valid attendance logs to insert.',
            ]);
        }

        // ✅ Step 4: Apply each student's punches, earliest first, through the
        // shared state machine — one row-locked transaction per punch (the same
        // guarantee scan() relies on), not one lock held across the whole batch.
        $results = [];
        foreach ($punchesByStudent as $studentId => $punches) {
            usort($punches, fn ($a, $b) => $a['punch_at']->timestamp <=> $b['punch_at']->timestamp);

            foreach ($punches as $punch) {
                $student = $punch['student'];

                try {
                    $outcome = app(QrAttendanceService::class)->recordPunch(
                        (int) $student->institute_id,
                        (int) $student->branch_id,
                        (int) $student->student_id,
                        (int) $student->class_id,
                        (int) $student->section_id,
                        $punch['punch_at'],
                    );
                } catch (\Throwable $e) {
                    // A single malformed/stale punch (e.g. a class/section that no
                    // longer exists) must never lose the rest of a genuine batch.
                    Log::error('Device attendance punch skipped: '.$e->getMessage(), ['student_id' => $studentId]);

                    continue;
                }

                $attendance = $outcome['attendance'];
                $results[] = [
                    'qr_code' => $punch['qr_code'],
                    'student_id' => (int) $studentId,
                    'status' => match ($outcome['status']) {
                        QrAttendanceService::STATUS_CHECKED_IN => 'check_in',
                        QrAttendanceService::STATUS_CHECKED_OUT => 'check_out',
                        QrAttendanceService::STATUS_ALREADY_CHECKED_OUT => 'already_checked_out',
                        default => $outcome['status'],
                    },
                    'check_in_at' => $attendance?->check_in_at?->toDateTimeString(),
                    'check_out_at' => $attendance?->check_out_at?->toDateTimeString(),
                ];
            }
        }

        // ✅ Step 5: Record raw IN/OUT punch logs + queue parent WhatsApp notifications.
        // Isolated in its own try/catch so a logging/notification failure can never
        // fail or roll back the attendance records already processed above.
        try {
            $this->processDevicePunchLogs($logs, $students);
        } catch (\Throwable $e) {
            Log::error('Device attendance IN/OUT + WhatsApp notification failed: '.$e->getMessage());
        }

        return response()->json([
            'status' => true,
            'message' => count($results).' attendance records processed successfully.',
            'inserted' => count($results),
            'results' => $results,
        ]);
    }

    /**
     * Turn the synced device logs into IN/OUT punch records and queue a WhatsApp
     * notification to each parent. Runs after the daily register upsert; every step
     * is tenant-explicit because this endpoint is public (no authenticated user).
     *
     * @param  Collection  $students  keyed by qr_code
     */
    private function processDevicePunchLogs(array $logs, $students): void
    {
        $punches = [];

        foreach ($logs as $log) {
            $qrCode = $log['id'] ?? null;
            $checkTime = $log['timestamp'] ?? null;

            if (! $qrCode || ! $checkTime || ! isset($students[$qrCode])) {
                continue;
            }

            $student = $students[$qrCode];
            $timestamp = Carbon::parse($checkTime);

            $punches[] = [
                'institute_id' => $student->institute_id,
                'branch_id' => $student->branch_id,
                'student_id' => $student->student_id,
                'class_id' => $student->class_id,
                'section_id' => $student->section_id,
                'date' => $timestamp->toDateString(),
                'time' => $timestamp->format('H:i:s'),
                'qr_code' => $qrCode,
                // Parent WhatsApp number: designated contact first, then the
                // registered parent phone, then the student's own phone.
                'phone' => $student->information_sent_to_phone ?: ($student->parent_phone ?: $student->phone),
                'student_name' => trim($student->first_name.' '.$student->last_name),
                'roll' => $student->roll ?? null,
            ];
        }

        if (empty($punches)) {
            return;
        }

        $notifications = app(AttendanceInOutService::class)->processBatch($punches);

        $whatsApp = app(WhatsAppService::class);
        foreach ($notifications as $notification) {
            if (empty($notification['phone'])) {
                continue;
            }

            try {
                $whatsApp->queueForTenant(
                    $notification['institute_id'],
                    $notification['branch_id'],
                    $notification['phone'],
                    $notification['event'],
                    $notification['parameters'],
                    $notification['student_id'],
                    AttendanceInOutService::REFERENCE_TYPE,
                    $notification['log_id']
                );
            } catch (\Throwable $e) {
                Log::error('Attendance WhatsApp queue failed: '.$e->getMessage(), ['student_id' => $notification['student_id']]);
            }
        }
    }
}
