<?php

use App\Http\Middleware\EnsureAttendanceDevice;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;

/**
 * POST /api/student-device-attendance used to sit outside every auth group with
 * no middleware and no rate limit, and resolved students by matching
 * student_sessions.qr_code across ALL institutes. Module 4 prints that same QR
 * code onto the student ID card, so anyone holding a card could forge attendance
 * and trigger a WhatsApp message to that student's parent.
 */
beforeEach(function () {
    // A second tenant to prove the endpoint cannot reach across institutes.
    DB::table('institutes')->updateOrInsert(
        ['id' => 2],
        ['name' => 'Other Institute', 'domain' => 'other.local', 'platform' => 'WEB', 'status' => 1],
    );
    DB::table('branches')->updateOrInsert(
        ['id' => 2],
        ['institute_id' => 2, 'name' => 'Other Branch', 'status' => 1],
    );

    DB::table('academic_years')->updateOrInsert(
        ['id' => 1],
        ['session' => '2026', 'year' => '2026'],
    );
    DB::table('classes')->updateOrInsert(
        ['id' => 1],
        ['institute_id' => 1, 'branch_id' => 1, 'class_name' => 'One'],
    );
    DB::table('sections')->updateOrInsert(
        ['id' => 1],
        ['institute_id' => 1, 'branch_id' => 1, 'class_id' => 1, 'section_name' => 'A'],
    );
    // studentDeviceAttendance() writes the daily register at period_id = 1.
    DB::table('periods')->updateOrInsert(
        ['id' => 1],
        ['institute_id' => 1, 'branch_id' => 1, 'period' => 'Period 1'],
    );
    DB::table('users')->updateOrInsert(
        ['id' => 90],
        [
            'institute_id' => 1, 'branch_id' => 1, 'name' => 'Punch Student',
            'phone' => '01911110000', 'email' => 'punch@test.local',
            'password' => bcrypt('12345678'), 'user_type' => 'Student', 'status' => 1,
        ],
    );
    DB::table('students')->updateOrInsert(
        ['id' => 90],
        [
            'institute_id' => 1, 'branch_id' => 1, 'user_id' => 90,
            'first_name' => 'Punch', 'last_name' => 'Student', 'phone' => '01911110000',
        ],
    );
    DB::table('student_sessions')->updateOrInsert(
        ['id' => 90],
        [
            'institute_id' => 1, 'branch_id' => 1, 'session_id' => 1, 'student_id' => 90,
            'class_id' => 1, 'section_id' => 1, 'roll' => '1',
            'qr_code' => 'QR-PUNCH-TEST-90',
        ],
    );
});

function devicePunch(array $headers = []): TestResponse
{
    return test()->withHeaders($headers)->postJson('/api/student-device-attendance', [
        'logs' => [
            ['id' => 'QR-PUNCH-TEST-90', 'timestamp' => now()->format('Y-m-d').' 08:15:00'],
        ],
    ]);
}

function mintDeviceToken(int $instituteId, string $token): void
{
    DB::table('settings')->insert([
        'institute_id' => $instituteId,
        'branch_id' => null,
        'name' => EnsureAttendanceDevice::SETTING_NAME,
        'value' => $token,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    clear_settings_map();
}

it('rejects a punch with no device token', function () {
    devicePunch()->assertStatus(401);

    expect(DB::table('student_attendances')->count())->toBe(0);
});

it('rejects a punch with an unknown device token', function () {
    mintDeviceToken(1, str_repeat('a', 64));

    devicePunch(['X-Device-Token' => str_repeat('b', 64)])->assertStatus(401);

    expect(DB::table('student_attendances')->count())->toBe(0);
});

it('accepts a punch from the institute that owns the token', function () {
    mintDeviceToken(1, str_repeat('a', 64));

    devicePunch(['X-Device-Token' => str_repeat('a', 64)])->assertOk();

    expect(DB::table('student_attendances')->where('student_id', 90)->count())->toBe(1);
});

it('will not let one institute\'s device write attendance for another institute\'s student', function () {
    // Institute 2 holds a perfectly valid token, and presents a QR code belonging
    // to institute 1 — exactly what lifting a printed ID card would give an attacker.
    mintDeviceToken(2, str_repeat('c', 64));

    devicePunch(['X-Device-Token' => str_repeat('c', 64)])->assertOk();

    expect(DB::table('student_attendances')->count())->toBe(0);
});

it('accepts an untokenised device only while strict mode is explicitly disabled', function () {
    config(['attendance.device_strict' => false]);

    devicePunch()->assertOk();

    expect(DB::table('student_attendances')->where('student_id', 90)->count())->toBe(1);
});
