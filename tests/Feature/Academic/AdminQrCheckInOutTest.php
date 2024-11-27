<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Passport;
use Modules\Academic\Models\StudentAttendance;
use Modules\Authentication\Models\User;
use Modules\ParentModule\Models\ParentModel;
use Tests\Support\AcademicFixtures;

/*
|--------------------------------------------------------------------------
| Admin/staff QR attendance — integration with the shared check-in/out
| state machine.
|--------------------------------------------------------------------------
| Closes the integration gap flagged by the Phase 8 final audit: Admin's WEB
| (Modules\Academic\Http\Controllers\WEB\QrAttendanceController) and API
| (APIAttendanceController::studentQrCodeAttendance) scan endpoints used to
| have their own independent exists()/create() attendance logic and never
| set check_in_at/check_out_at. Both now route through the SAME
| App\Services\QrAttendanceService::scan() the Teacher flow uses, with no
| expected class/section (preserving Admin's original unrestricted-scan
| behavior — Admin was never limited to a teacher's own sections).
*/

beforeEach(function () {
    AcademicFixtures::build();
    DB::table('periods')->updateOrInsert(['id' => 1], ['institute_id' => 1, 'branch_id' => 1, 'period' => 'Period 1']);
});

function adminQrStudent(int $tenant, string $qrCode): array
{
    $student = AcademicFixtures::student($tenant, (string) random_int(1, 999));
    DB::table('student_sessions')->where('id', $student['student_session_id'])->update(['qr_code' => $qrCode]);

    return $student;
}

// ── Admin WEB — first / second / third scan ───────────────────────────────

test('an admin first scan via WEB creates the attendance row and sets check_in_at only', function () {
    $admin = AcademicFixtures::user('Admin');
    $student = adminQrStudent(1, 'ADMIN-WEB-1');
    $this->actingAs($admin);

    $this->postJson('/qrattendance/scan', ['qr_code' => 'ADMIN-WEB-1'])
        ->assertOk()
        ->assertJsonPath('status', true)
        ->assertJsonPath('data.status', 'check_in')
        ->assertJsonPath('data.check_out_at', null);

    $attendance = StudentAttendance::where('student_id', $student['student_id'])->first();
    expect($attendance->attendance)->toBe(1)
        ->and($attendance->check_in_at)->not->toBeNull()
        ->and($attendance->check_out_at)->toBeNull();
});

test('an admin second scan via WEB sets check_out_at on the same row', function () {
    $admin = AcademicFixtures::user('Admin');
    $student = adminQrStudent(1, 'ADMIN-WEB-2');
    $this->actingAs($admin);

    $first = $this->postJson('/qrattendance/scan', ['qr_code' => 'ADMIN-WEB-2'])->assertOk();
    $checkInAt = $first->json('data.check_in_at');

    $this->postJson('/qrattendance/scan', ['qr_code' => 'ADMIN-WEB-2'])
        ->assertOk()
        ->assertJsonPath('data.status', 'check_out')
        ->assertJsonPath('data.check_in_at', $checkInAt); // unchanged

    expect(StudentAttendance::where('student_id', $student['student_id'])->count())->toBe(1);
    $attendance = StudentAttendance::where('student_id', $student['student_id'])->first();
    expect($attendance->check_out_at)->not->toBeNull();
});

test('an admin third scan via WEB changes nothing and reports already checked out', function () {
    $admin = AcademicFixtures::user('Admin');
    $student = adminQrStudent(1, 'ADMIN-WEB-3');
    $this->actingAs($admin);

    $this->postJson('/qrattendance/scan', ['qr_code' => 'ADMIN-WEB-3'])->assertOk();
    $second = $this->postJson('/qrattendance/scan', ['qr_code' => 'ADMIN-WEB-3'])->assertOk();

    $this->postJson('/qrattendance/scan', ['qr_code' => 'ADMIN-WEB-3'])
        ->assertOk()
        ->assertJsonPath('data.status', 'already_checked_out')
        ->assertJsonPath('data.check_in_at', $second->json('data.check_in_at'))
        ->assertJsonPath('data.check_out_at', $second->json('data.check_out_at'));

    expect(StudentAttendance::where('student_id', $student['student_id'])->count())->toBe(1);
});

// ── Admin API — first / second / third scan ───────────────────────────────

test('an admin first scan via API creates the attendance row and sets check_in_at only', function () {
    $admin = AcademicFixtures::user('Admin');
    $student = adminQrStudent(1, 'ADMIN-API-1');
    Passport::actingAs($admin, [], 'api');

    $this->postJson('/api/student-qr-code-attendance', ['qr_code' => 'ADMIN-API-1'])
        ->assertOk()
        ->assertJsonPath('data.status', 'check_in')
        ->assertJsonPath('data.check_out_at', null);

    $attendance = StudentAttendance::where('student_id', $student['student_id'])->first();
    expect($attendance->attendance)->toBe(1)
        ->and($attendance->check_in_at)->not->toBeNull()
        ->and($attendance->check_out_at)->toBeNull();
});

test('an admin second scan via API sets check_out_at on the same row', function () {
    $admin = AcademicFixtures::user('Admin');
    $student = adminQrStudent(1, 'ADMIN-API-2');
    Passport::actingAs($admin, [], 'api');

    $first = $this->postJson('/api/student-qr-code-attendance', ['qr_code' => 'ADMIN-API-2'])->assertOk();
    $checkInAt = $first->json('data.check_in_at');

    $this->postJson('/api/student-qr-code-attendance', ['qr_code' => 'ADMIN-API-2'])
        ->assertOk()
        ->assertJsonPath('data.status', 'check_out')
        ->assertJsonPath('data.check_in_at', $checkInAt);

    expect(StudentAttendance::where('student_id', $student['student_id'])->count())->toBe(1);
});

test('an admin third scan via API changes nothing and reports already checked out', function () {
    $admin = AcademicFixtures::user('Admin');
    $student = adminQrStudent(1, 'ADMIN-API-3');
    Passport::actingAs($admin, [], 'api');

    $this->postJson('/api/student-qr-code-attendance', ['qr_code' => 'ADMIN-API-3'])->assertOk();
    $second = $this->postJson('/api/student-qr-code-attendance', ['qr_code' => 'ADMIN-API-3'])->assertOk();

    $this->postJson('/api/student-qr-code-attendance', ['qr_code' => 'ADMIN-API-3'])
        ->assertOk()
        ->assertJsonPath('data.status', 'already_checked_out')
        ->assertJsonPath('data.check_out_at', $second->json('data.check_out_at'));

    expect(StudentAttendance::where('student_id', $student['student_id'])->count())->toBe(1);
});

// ── Mixed Admin/Teacher flow — the actual integration gap being closed ────

test('admin check-in followed by teacher check-out lands on the same row', function () {
    $admin = AcademicFixtures::user('Admin');
    $teacherUser = AcademicFixtures::teacher(subjectId: 1, sectionId: 1);
    $student = adminQrStudent(1, 'MIXED-1');
    DB::table('student_sessions')->where('id', $student['student_session_id'])->update(['class_id' => 1, 'section_id' => 1]);

    $this->actingAs($admin);
    $checkIn = $this->postJson('/qrattendance/scan', ['qr_code' => 'MIXED-1'])
        ->assertOk()
        ->assertJsonPath('data.status', 'check_in');

    Passport::actingAs($teacherUser, [], 'api');
    $this->postJson('/api/teacher/qr-attendance/scan', ['class_id' => 1, 'section_id' => 1, 'qr_code' => 'MIXED-1'])
        ->assertOk()
        ->assertJsonPath('data.status', 'check_out')
        ->assertJsonPath('data.check_in_at', $checkIn->json('data.check_in_at'));

    expect(StudentAttendance::where('student_id', $student['student_id'])->count())->toBe(1);
});

test('teacher check-in followed by admin check-out lands on the same row', function () {
    $admin = AcademicFixtures::user('Admin');
    $teacherUser = AcademicFixtures::teacher(subjectId: 1, sectionId: 1);
    $student = adminQrStudent(1, 'MIXED-2');
    DB::table('student_sessions')->where('id', $student['student_session_id'])->update(['class_id' => 1, 'section_id' => 1]);

    Passport::actingAs($teacherUser, [], 'api');
    $checkIn = $this->postJson('/api/teacher/qr-attendance/scan', ['class_id' => 1, 'section_id' => 1, 'qr_code' => 'MIXED-2'])
        ->assertOk()
        ->assertJsonPath('data.status', 'check_in');

    $this->actingAs($admin);
    $this->postJson('/qrattendance/scan', ['qr_code' => 'MIXED-2'])
        ->assertOk()
        ->assertJsonPath('data.status', 'check_out')
        ->assertJsonPath('data.check_in_at', $checkIn->json('data.check_in_at'));

    expect(StudentAttendance::where('student_id', $student['student_id'])->count())->toBe(1);
});

test('admin check-in followed by admin check-out lands on the same row', function () {
    $admin = AcademicFixtures::user('Admin');
    $student = adminQrStudent(1, 'MIXED-3');
    $this->actingAs($admin);

    $checkIn = $this->postJson('/qrattendance/scan', ['qr_code' => 'MIXED-3'])
        ->assertOk()
        ->assertJsonPath('data.status', 'check_in');

    $this->postJson('/qrattendance/scan', ['qr_code' => 'MIXED-3'])
        ->assertOk()
        ->assertJsonPath('data.status', 'check_out')
        ->assertJsonPath('data.check_in_at', $checkIn->json('data.check_in_at'));

    expect(StudentAttendance::where('student_id', $student['student_id'])->count())->toBe(1);
});

// ── Parent Portal reflects Admin-scanned check-in/out ─────────────────────

test('a student checked in/out via Admin QR shows check-in/out in the Parent Portal', function () {
    $admin = AcademicFixtures::user('Admin');
    $student = adminQrStudent(1, 'ADMIN-PARENT');

    $parentUser = User::factory()->create([
        'user_type' => 'Parent', 'institute_id' => 1, 'branch_id' => 1, 'status' => 1,
        'password' => Hash::make('secret123'),
    ]);
    $parent = ParentModel::create([
        'institute_id' => 1, 'branch_id' => 1, 'user_id' => $parentUser->id,
        'student_id' => $student['student_id'], 'parent_name' => 'Parent Name',
        'parent_phone' => '017'.str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT),
        'access_key' => 'secret123',
    ]);
    DB::table('students')->where('id', $student['student_id'])->update(['parent_id' => $parent->id]);

    $this->actingAs($admin);
    $this->postJson('/qrattendance/scan', ['qr_code' => 'ADMIN-PARENT'])->assertOk(); // check-in

    Passport::actingAs($parentUser, [], 'api');
    $afterCheckIn = $this->postJson('/api/parent/my-children/attendance', ['month' => now()->month, 'year' => now()->year])->assertOk();
    $today = collect($afterCheckIn->json('days'))->firstWhere('date', now()->toDateString());
    expect($today['check_in_at'])->not->toBeNull();
    expect($today['check_out_at'])->toBeNull();

    $this->actingAs($admin);
    $this->postJson('/qrattendance/scan', ['qr_code' => 'ADMIN-PARENT'])->assertOk(); // check-out

    Passport::actingAs($parentUser, [], 'api');
    $afterCheckOut = $this->postJson('/api/parent/my-children/attendance', ['month' => now()->month, 'year' => now()->year])->assertOk();
    $todayAfter = collect($afterCheckOut->json('days'))->firstWhere('date', now()->toDateString());
    expect($todayAfter['check_out_at'])->not->toBeNull();
});

// ── Authorization / tenant isolation — unchanged from before this change ──

test('an unauthenticated request to the Admin QR routes is rejected', function () {
    $this->postJson('/qrattendance/scan', ['qr_code' => 'irrelevant'])->assertStatus(401);
    $this->postJson('/api/student-qr-code-attendance', ['qr_code' => 'irrelevant'])->assertStatus(401);
});

test('an authenticated Admin can still scan, matching pre-existing route-level authorization', function () {
    $admin = AcademicFixtures::user('Admin');
    $student = adminQrStudent(1, 'ADMIN-AUTH-OK');
    $this->actingAs($admin);

    $this->postJson('/qrattendance/scan', ['qr_code' => 'ADMIN-AUTH-OK'])->assertOk()->assertJsonPath('status', true);
    expect(StudentAttendance::where('student_id', $student['student_id'])->exists())->toBeTrue();
});

test('a QR code belonging to another institute is not found via Admin QR (tenant scoping)', function () {
    $admin = AcademicFixtures::user('Admin', 1);
    $studentInTenant2 = adminQrStudent(2, 'ADMIN-CROSS-TENANT');
    $this->actingAs($admin);

    $this->postJson('/qrattendance/scan', ['qr_code' => 'ADMIN-CROSS-TENANT'])->assertStatus(404);

    expect(StudentAttendance::where('student_id', $studentInTenant2['student_id'])->exists())->toBeFalse();
});
