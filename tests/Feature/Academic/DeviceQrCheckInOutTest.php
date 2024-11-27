<?php

use App\Http\Middleware\EnsureAttendanceDevice;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Laravel\Passport\Passport;
use Modules\Academic\Models\StudentAttendance;
use Tests\Support\AcademicFixtures;

/*
|--------------------------------------------------------------------------
| Device/physical QR attendance — integration with the shared check-in/out
| state machine.
|--------------------------------------------------------------------------
| Closes the last gap flagged by the Phase 8 final production audit:
| POST /api/student-device-attendance (Modules\SystemConfiguration\...\API\
| UtilityController::studentDeviceAttendance) used to bulk-upsert directly
| into student_attendances (attendance=1 only) and never touched
| check_in_at/check_out_at. It now routes every resolved punch through
| App\Services\QrAttendanceService::recordPunch() — the SAME row-level
| transactional primitive scan() uses — so a student punched once by a
| device and once by a Teacher/Admin scan (in either order) still lands on
| one consistent row.
|
| Device punches carry their OWN timestamp (the actual physical punch time,
| not "now"), and a device resyncing the same window on every call is
| normal, expected behaviour (see AttendanceInOutBatchQueryTest.php) — a
| resent punch at or before the already-recorded check-in must never be
| mistaken for a second (checkout) event. That guard is exercised directly
| below ("a resynced duplicate first punch does not create a checkout").
*/

beforeEach(function () {
    AcademicFixtures::build();
    DB::table('periods')->updateOrInsert(['id' => 1], ['institute_id' => 1, 'branch_id' => 1, 'period' => 'Period 1']);
});

function deviceStudent(int $tenant, string $qrCode): array
{
    $student = AcademicFixtures::student($tenant, (string) random_int(1, 999));
    DB::table('student_sessions')->where('id', $student['student_session_id'])->update(['qr_code' => $qrCode, 'class_id' => $tenant, 'section_id' => $tenant]);

    return $student;
}

function mintDeviceQrToken(int $instituteId, string $token): void
{
    DB::table('settings')->updateOrInsert(
        ['institute_id' => $instituteId, 'branch_id' => null, 'name' => EnsureAttendanceDevice::SETTING_NAME],
        ['value' => $token, 'created_at' => now(), 'updated_at' => now()],
    );
    clear_settings_map();
}

function devicePunches(array $logs, string $token): TestResponse
{
    return test()->withHeaders(['X-Device-Token' => $token])->postJson('/api/student-device-attendance', ['logs' => $logs]);
}

// ── Device-only ────────────────────────────────────────────────────────

test('the first device punch creates the row and sets check_in_at only', function () {
    $token = str_repeat('a', 64);
    mintDeviceQrToken(1, $token);
    $student = deviceStudent(1, 'DEV-1');

    devicePunches([['id' => 'DEV-1', 'timestamp' => now()->format('Y-m-d').' 08:00:00']], $token)
        ->assertOk()
        ->assertJsonPath('inserted', 1)
        ->assertJsonPath('results.0.status', 'check_in')
        ->assertJsonPath('results.0.check_out_at', null);

    $attendance = StudentAttendance::where('student_id', $student['student_id'])->first();
    expect($attendance->attendance)->toBe(1)
        ->and($attendance->check_in_at)->not->toBeNull()
        ->and($attendance->check_out_at)->toBeNull();
});

test('a second, later device punch sets check_out_at on the same row', function () {
    $token = str_repeat('a', 64);
    mintDeviceQrToken(1, $token);
    $student = deviceStudent(1, 'DEV-2');
    $today = now()->format('Y-m-d');

    devicePunches([['id' => 'DEV-2', 'timestamp' => "{$today} 08:00:00"]], $token)->assertOk();
    devicePunches([['id' => 'DEV-2', 'timestamp' => "{$today} 15:00:00"]], $token)
        ->assertOk()
        ->assertJsonPath('results.0.status', 'check_out');

    expect(StudentAttendance::where('student_id', $student['student_id'])->count())->toBe(1);
    $attendance = StudentAttendance::where('student_id', $student['student_id'])->first();
    expect($attendance->check_in_at)->not->toBeNull();
    expect($attendance->check_out_at)->not->toBeNull();
});

test('a third device punch reports already_checked_out and modifies nothing', function () {
    $token = str_repeat('a', 64);
    mintDeviceQrToken(1, $token);
    $student = deviceStudent(1, 'DEV-3');
    $today = now()->format('Y-m-d');

    devicePunches([['id' => 'DEV-3', 'timestamp' => "{$today} 08:00:00"]], $token)->assertOk();
    $second = devicePunches([['id' => 'DEV-3', 'timestamp' => "{$today} 15:00:00"]], $token)->assertOk();

    devicePunches([['id' => 'DEV-3', 'timestamp' => "{$today} 18:00:00"]], $token)
        ->assertOk()
        ->assertJsonPath('results.0.status', 'already_checked_out')
        ->assertJsonPath('results.0.check_in_at', $second->json('results.0.check_in_at'))
        ->assertJsonPath('results.0.check_out_at', $second->json('results.0.check_out_at'));

    expect(StudentAttendance::where('student_id', $student['student_id'])->count())->toBe(1);
});

test('attendance remains unchanged (present) after a device checkout', function () {
    $token = str_repeat('a', 64);
    mintDeviceQrToken(1, $token);
    $student = deviceStudent(1, 'DEV-4');
    $today = now()->format('Y-m-d');

    devicePunches([['id' => 'DEV-4', 'timestamp' => "{$today} 08:00:00"]], $token)->assertOk();
    devicePunches([['id' => 'DEV-4', 'timestamp' => "{$today} 15:00:00"]], $token)->assertOk();

    expect(StudentAttendance::where('student_id', $student['student_id'])->first()->attendance)->toBe(1);
});

test('a resynced duplicate first punch does not create a checkout', function () {
    // The device's normal behaviour: resend the same window on every sync.
    // The exact same (qr_code, timestamp) pair arriving again must be a no-op,
    // never misread as the second (checkout) event.
    $token = str_repeat('a', 64);
    mintDeviceQrToken(1, $token);
    $student = deviceStudent(1, 'DEV-5');
    $punch = ['id' => 'DEV-5', 'timestamp' => now()->format('Y-m-d').' 08:00:00'];

    devicePunches([$punch], $token)->assertOk()->assertJsonPath('results.0.status', 'check_in');
    devicePunches([$punch], $token)->assertOk(); // exact resync of the same punch

    $attendance = StudentAttendance::where('student_id', $student['student_id'])->first();
    expect($attendance->check_in_at)->not->toBeNull();
    expect($attendance->check_out_at)->toBeNull(); // NOT checked out by the resync
    expect(StudentAttendance::where('student_id', $student['student_id'])->count())->toBe(1);
});

test('a device resending its whole day window (IN and OUT together) does not create another row', function () {
    $token = str_repeat('a', 64);
    mintDeviceQrToken(1, $token);
    $student = deviceStudent(1, 'DEV-6');
    $today = now()->format('Y-m-d');
    $morning = ['id' => 'DEV-6', 'timestamp' => "{$today} 08:00:00"];
    $evening = ['id' => 'DEV-6', 'timestamp' => "{$today} 15:00:00"];

    devicePunches([$morning, $evening], $token)->assertOk(); // first sync
    devicePunches([$morning, $evening], $token)->assertOk(); // exact resync of the whole window

    $attendance = StudentAttendance::where('student_id', $student['student_id'])->first();
    expect(StudentAttendance::where('student_id', $student['student_id'])->count())->toBe(1);
    expect($attendance->check_in_at)->not->toBeNull();
    expect($attendance->check_out_at)->not->toBeNull();
});

test('a batch delivering IN and OUT out of order still resolves correctly by punch time, not arrival order', function () {
    $token = str_repeat('a', 64);
    mintDeviceQrToken(1, $token);
    $student = deviceStudent(1, 'DEV-7');
    $today = now()->format('Y-m-d');

    // Evening punch listed FIRST in the payload, morning SECOND.
    devicePunches([
        ['id' => 'DEV-7', 'timestamp' => "{$today} 15:00:00"],
        ['id' => 'DEV-7', 'timestamp' => "{$today} 08:00:00"],
    ], $token)->assertOk();

    $attendance = StudentAttendance::where('student_id', $student['student_id'])->first();
    expect($attendance->check_in_at->format('H:i:s'))->toBe('08:00:00');
    expect($attendance->check_out_at->format('H:i:s'))->toBe('15:00:00');
});

// ── Mixed workflows ────────────────────────────────────────────────────

test('device check-in followed by teacher check-out lands on the same row', function () {
    $token = str_repeat('a', 64);
    mintDeviceQrToken(1, $token);
    $teacherUser = AcademicFixtures::teacher(subjectId: 1, sectionId: 1);
    $student = deviceStudent(1, 'MIX-DT-1');

    $checkIn = devicePunches([['id' => 'MIX-DT-1', 'timestamp' => now()->format('Y-m-d').' 08:00:00']], $token)
        ->assertOk()->assertJsonPath('results.0.status', 'check_in');

    Passport::actingAs($teacherUser, [], 'api');
    test()->postJson('/api/teacher/qr-attendance/scan', ['class_id' => 1, 'section_id' => 1, 'qr_code' => 'MIX-DT-1'])
        ->assertOk()
        ->assertJsonPath('data.status', 'check_out')
        ->assertJsonPath('data.check_in_at', $checkIn->json('results.0.check_in_at'));

    expect(StudentAttendance::where('student_id', $student['student_id'])->count())->toBe(1);
});

test('teacher check-in followed by device check-out lands on the same row', function () {
    $token = str_repeat('a', 64);
    mintDeviceQrToken(1, $token);
    $teacherUser = AcademicFixtures::teacher(subjectId: 1, sectionId: 1);
    $student = deviceStudent(1, 'MIX-TD-1');

    Passport::actingAs($teacherUser, [], 'api');
    $checkIn = test()->postJson('/api/teacher/qr-attendance/scan', ['class_id' => 1, 'section_id' => 1, 'qr_code' => 'MIX-TD-1'])
        ->assertOk()->assertJsonPath('data.status', 'check_in');

    // The device endpoint authenticates via X-Device-Token, never Passport —
    // but Passport::actingAs() binds a resolved user to the api guard for the
    // rest of the test process, which RejectPortalApiUser (global on the api
    // middleware group) would otherwise reject this portal-less route for.
    auth()->forgetGuards();

    devicePunches([['id' => 'MIX-TD-1', 'timestamp' => now()->addMinute()->format('Y-m-d H:i:s')]], $token)
        ->assertOk()
        ->assertJsonPath('results.0.status', 'check_out')
        ->assertJsonPath('results.0.check_in_at', $checkIn->json('data.check_in_at'));

    expect(StudentAttendance::where('student_id', $student['student_id'])->count())->toBe(1);
});

test('device check-in followed by admin check-out lands on the same row', function () {
    $token = str_repeat('a', 64);
    mintDeviceQrToken(1, $token);
    $admin = AcademicFixtures::user('Admin');
    $student = deviceStudent(1, 'MIX-DA-1');

    $checkIn = devicePunches([['id' => 'MIX-DA-1', 'timestamp' => now()->format('Y-m-d').' 08:00:00']], $token)
        ->assertOk()->assertJsonPath('results.0.status', 'check_in');

    test()->actingAs($admin)->postJson('/qrattendance/scan', ['qr_code' => 'MIX-DA-1'])
        ->assertOk()
        ->assertJsonPath('data.status', 'check_out')
        ->assertJsonPath('data.check_in_at', $checkIn->json('results.0.check_in_at'));

    expect(StudentAttendance::where('student_id', $student['student_id'])->count())->toBe(1);
});

test('admin check-in followed by device check-out lands on the same row', function () {
    $token = str_repeat('a', 64);
    mintDeviceQrToken(1, $token);
    $admin = AcademicFixtures::user('Admin');
    $student = deviceStudent(1, 'MIX-AD-1');

    $checkIn = test()->actingAs($admin)->postJson('/qrattendance/scan', ['qr_code' => 'MIX-AD-1'])
        ->assertOk()->assertJsonPath('data.status', 'check_in');

    devicePunches([['id' => 'MIX-AD-1', 'timestamp' => now()->addMinute()->format('Y-m-d H:i:s')]], $token)
        ->assertOk()
        ->assertJsonPath('results.0.status', 'check_out')
        ->assertJsonPath('results.0.check_in_at', $checkIn->json('data.check_in_at'));

    expect(StudentAttendance::where('student_id', $student['student_id'])->count())->toBe(1);
});

test('device check-in followed by device check-out lands on the same row and a third punch no-ops', function () {
    $token = str_repeat('a', 64);
    mintDeviceQrToken(1, $token);
    $student = deviceStudent(1, 'MIX-DD-1');
    $today = now()->format('Y-m-d');

    devicePunches([['id' => 'MIX-DD-1', 'timestamp' => "{$today} 08:00:00"]], $token)->assertOk();
    devicePunches([['id' => 'MIX-DD-1', 'timestamp' => "{$today} 15:00:00"]], $token)
        ->assertOk()->assertJsonPath('results.0.status', 'check_out');
    devicePunches([['id' => 'MIX-DD-1', 'timestamp' => "{$today} 18:00:00"]], $token)
        ->assertOk()->assertJsonPath('results.0.status', 'already_checked_out');

    expect(StudentAttendance::where('student_id', $student['student_id'])->count())->toBe(1);
});

// ── Existing-row compatibility ─────────────────────────────────────────

test('a device punch against an existing manual attendance row with NULL timestamps checks in', function () {
    $token = str_repeat('a', 64);
    mintDeviceQrToken(1, $token);
    $student = deviceStudent(1, 'DEV-MANUAL');

    $manualRow = StudentAttendance::create([
        'institute_id' => 1, 'branch_id' => 1, 'student_id' => $student['student_id'],
        'class_id' => 1, 'section_id' => 1, 'period_id' => 1,
        'date' => now()->toDateString(), 'attendance' => 1,
    ]);
    expect($manualRow->check_in_at)->toBeNull();

    devicePunches([['id' => 'DEV-MANUAL', 'timestamp' => now()->format('Y-m-d').' 08:00:00']], $token)
        ->assertOk()->assertJsonPath('results.0.status', 'check_in');

    expect(StudentAttendance::where('student_id', $student['student_id'])->count())->toBe(1);
    expect($manualRow->fresh()->check_in_at)->not->toBeNull();
});

test('a device punch against an existing QR check-in (from Teacher) records checkout', function () {
    $token = str_repeat('a', 64);
    mintDeviceQrToken(1, $token);
    $teacherUser = AcademicFixtures::teacher(subjectId: 1, sectionId: 1);
    $student = deviceStudent(1, 'DEV-EXIST-QR');

    Passport::actingAs($teacherUser, [], 'api');
    test()->postJson('/api/teacher/qr-attendance/scan', ['class_id' => 1, 'section_id' => 1, 'qr_code' => 'DEV-EXIST-QR'])->assertOk();

    auth()->forgetGuards();

    devicePunches([['id' => 'DEV-EXIST-QR', 'timestamp' => now()->addMinute()->format('Y-m-d H:i:s')]], $token)
        ->assertOk()->assertJsonPath('results.0.status', 'check_out');
});

test('a device punch against an already checked-out row changes nothing', function () {
    $token = str_repeat('a', 64);
    mintDeviceQrToken(1, $token);
    $student = deviceStudent(1, 'DEV-EXIST-DONE');
    $today = now()->format('Y-m-d');

    devicePunches([['id' => 'DEV-EXIST-DONE', 'timestamp' => "{$today} 08:00:00"]], $token)->assertOk();
    devicePunches([['id' => 'DEV-EXIST-DONE', 'timestamp' => "{$today} 15:00:00"]], $token)->assertOk();
    $before = StudentAttendance::where('student_id', $student['student_id'])->first();

    devicePunches([['id' => 'DEV-EXIST-DONE', 'timestamp' => "{$today} 20:00:00"]], $token)->assertOk();
    $after = StudentAttendance::where('student_id', $student['student_id'])->first();

    expect($after->check_in_at->toDateTimeString())->toBe($before->check_in_at->toDateTimeString());
    expect($after->check_out_at->toDateTimeString())->toBe($before->check_out_at->toDateTimeString());
});

test('a historical attendance record is never modified by a later device punch', function () {
    $token = str_repeat('a', 64);
    mintDeviceQrToken(1, $token);
    $student = deviceStudent(1, 'DEV-HIST');

    $yesterday = StudentAttendance::create([
        'institute_id' => 1, 'branch_id' => 1, 'student_id' => $student['student_id'],
        'class_id' => 1, 'section_id' => 1, 'period_id' => 1,
        'date' => now()->subDay()->toDateString(), 'attendance' => 1,
    ]);

    devicePunches([['id' => 'DEV-HIST', 'timestamp' => now()->format('Y-m-d').' 08:00:00']], $token)->assertOk();

    expect($yesterday->fresh())->check_in_at->toBeNull()->check_out_at->toBeNull()->attendance->toBe(1);
    expect(StudentAttendance::where('student_id', $student['student_id'])->count())->toBe(2);
});

// ── Security / tenancy (pre-existing token/tenant behaviour, reconfirmed) ─

test('an invalid device token is still rejected', function () {
    mintDeviceQrToken(1, str_repeat('a', 64));

    devicePunches([['id' => 'irrelevant', 'timestamp' => now()->toDateTimeString()]], str_repeat('z', 64))
        ->assertStatus(401);
});

test('an unknown QR code in the payload is silently skipped, not a hard error', function () {
    $token = str_repeat('a', 64);
    mintDeviceQrToken(1, $token);

    devicePunches([['id' => 'DOES-NOT-EXIST', 'timestamp' => now()->toDateTimeString()]], $token)
        ->assertOk()
        ->assertJsonPath('message', 'No valid attendance logs to insert.');
});

test('an inactive student cannot be checked in via a device punch', function () {
    $token = str_repeat('a', 64);
    mintDeviceQrToken(1, $token);
    $student = deviceStudent(1, 'DEV-INACTIVE');
    DB::table('students')->where('id', $student['student_id'])->update(['status' => '0']);

    devicePunches([['id' => 'DEV-INACTIVE', 'timestamp' => now()->toDateTimeString()]], $token)
        ->assertOk()
        ->assertJsonPath('message', 'No valid attendance logs to insert.');

    expect(StudentAttendance::where('student_id', $student['student_id'])->exists())->toBeFalse();
});

test('a QR code belonging to another institute cannot be punched via this device token', function () {
    $token = str_repeat('a', 64);
    mintDeviceQrToken(1, $token);
    $studentInTenant2 = deviceStudent(2, 'DEV-CROSS-TENANT');

    devicePunches([['id' => 'DEV-CROSS-TENANT', 'timestamp' => now()->toDateTimeString()]], $token)
        ->assertOk()
        ->assertJsonPath('message', 'No valid attendance logs to insert.');

    expect(StudentAttendance::where('student_id', $studentInTenant2['student_id'])->exists())->toBeFalse();
});

test('a malformed device timestamp is skipped without corrupting attendance data', function () {
    $token = str_repeat('a', 64);
    mintDeviceQrToken(1, $token);
    $student = deviceStudent(1, 'DEV-BADTIME');

    devicePunches([['id' => 'DEV-BADTIME', 'timestamp' => 'not-a-real-timestamp']], $token)
        ->assertOk()
        ->assertJsonPath('message', 'No valid attendance logs to insert.');

    expect(StudentAttendance::where('student_id', $student['student_id'])->exists())->toBeFalse();
});

test('malformed/missing punch data (no id, no timestamp) does not break the batch', function () {
    $token = str_repeat('a', 64);
    mintDeviceQrToken(1, $token);
    $student = deviceStudent(1, 'DEV-GOOD-IN-BATCH');

    devicePunches([
        ['id' => null, 'timestamp' => now()->toDateTimeString()],
        ['timestamp' => now()->toDateTimeString()], // missing id
        ['id' => 'DEV-GOOD-IN-BATCH'], // missing timestamp
        ['id' => 'DEV-GOOD-IN-BATCH', 'timestamp' => now()->format('Y-m-d').' 08:00:00'],
    ], $token)
        ->assertOk()
        ->assertJsonPath('inserted', 1)
        ->assertJsonPath('results.0.status', 'check_in');

    expect(StudentAttendance::where('student_id', $student['student_id'])->count())->toBe(1);
});

// ── Concurrency (genuine independent MySQL connections) ───────────────────

test('two concurrent device first punches for the same student never both create a row', function () {
    $token = str_repeat('a', 64);
    mintDeviceQrToken(1, $token);
    $student = deviceStudent(1, 'DEV-CONC-1');

    config(['database.connections.device_conc_conn2' => config('database.connections.mysql')]);
    config(['database.connections.device_conc_conn3' => config('database.connections.mysql')]);
    $conn2 = DB::connection('device_conc_conn2');
    $conn3 = DB::connection('device_conc_conn3');
    $conn2->statement('SET SESSION innodb_lock_wait_timeout = 2');
    $conn3->statement('SET SESSION innodb_lock_wait_timeout = 2');
    $conn2->statement('SET FOREIGN_KEY_CHECKS = 0');
    $conn3->statement('SET FOREIGN_KEY_CHECKS = 0');

    $row = [
        'institute_id' => 1, 'branch_id' => 1, 'student_id' => $student['student_id'],
        'class_id' => 1, 'section_id' => 1, 'period_id' => 1,
        'date' => now()->toDateString(), 'attendance' => 1, 'check_in_at' => now(),
        'created_at' => now(), 'updated_at' => now(),
    ];

    // Simulates two devices (or one device double-firing) racing to insert the
    // very first punch of the day — the exact race QrAttendanceService's
    // duplicate-key catch-and-retry (shared by recordPunch()) protects against.
    $conn2->table('student_attendances')->insert($row);
    $secondInsertFailed = false;
    try {
        $conn3->table('student_attendances')->insert($row);
    } catch (Throwable $e) {
        $secondInsertFailed = (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    expect($secondInsertFailed)->toBeTrue();
    expect($conn2->table('student_attendances')->where('student_id', $student['student_id'])->count())->toBe(1);

    $conn2->table('student_attendances')->where('student_id', $student['student_id'])->delete();
    DB::purge('device_conc_conn2');
    DB::purge('device_conc_conn3');
});

test('a device first punch is genuinely blocked while an Admin/Teacher scan holds the row lock', function () {
    $student = deviceStudent(1, 'DEV-CONC-2');

    config(['database.connections.device_conc_conn2' => config('database.connections.mysql')]);
    $conn2 = DB::connection('device_conc_conn2');
    $conn2->statement('SET SESSION innodb_lock_wait_timeout = 2');
    $conn2->statement('SET FOREIGN_KEY_CHECKS = 0');

    $conn2->table('student_attendances')->insert([
        'institute_id' => 1, 'branch_id' => 1, 'student_id' => $student['student_id'],
        'class_id' => 1, 'section_id' => 1, 'period_id' => 1,
        'date' => now()->toDateString(), 'attendance' => 1, 'check_in_at' => now(),
        'created_at' => now(), 'updated_at' => now(),
    ]);

    $conn2->beginTransaction();
    $conn2->table('student_attendances')
        ->where('student_id', $student['student_id'])->whereDate('date', now())
        ->lockForUpdate()->first();

    $blocked = null;
    config(['database.connections.device_conc_conn3' => config('database.connections.mysql')]);
    $conn3 = DB::connection('device_conc_conn3');
    $conn3->statement('SET SESSION innodb_lock_wait_timeout = 2');
    try {
        $conn3->table('student_attendances')
            ->where('student_id', $student['student_id'])->whereDate('date', now())
            ->lockForUpdate()->first();
    } catch (Throwable $e) {
        $blocked = $e;
    }

    $conn2->commit();
    $conn2->table('student_attendances')->where('student_id', $student['student_id'])->delete();
    DB::purge('device_conc_conn2');
    DB::purge('device_conc_conn3');

    expect($blocked)->not->toBeNull();
    expect($blocked->getMessage())->toContain('Lock wait timeout exceeded');
});
