<?php

use Illuminate\Support\Facades\DB;
use Laravel\Passport\Passport;
use Modules\Academic\Models\StudentAttendance;
use Tests\Support\AcademicFixtures;

/*
|--------------------------------------------------------------------------
| Phase 7 — Teacher QR attendance scanning.
|--------------------------------------------------------------------------
*/

beforeEach(function () {
    AcademicFixtures::build();
    DB::table('periods')->updateOrInsert(['id' => 1], ['institute_id' => 1, 'branch_id' => 1, 'period' => 'Period 1']);
});

function qrStudentInSection(int $tenant, int $classId, int $sectionId, string $qrCode): array
{
    $student = AcademicFixtures::student($tenant, (string) random_int(1, 999), $classId, $sectionId);
    DB::table('student_sessions')->where('id', $student['student_session_id'])->update(['qr_code' => $qrCode]);

    return $student;
}

// ── WEB ──────────────────────────────────────────────────────────────────

test('a subject teacher assigned to the section can scan a student in it', function () {
    $teacherUser = AcademicFixtures::teacher(subjectId: 1, sectionId: 1); // assigned to class 1 / section 1
    $student = qrStudentInSection(1, 1, 1, 'QR-TEACHER-OK');

    $this->actingAs($teacherUser)->postJson('/teacher/qr-attendance/scan', [
        'class_id' => 1,
        'section_id' => 1,
        'qr_code' => 'QR-TEACHER-OK',
    ])->assertOk()->assertJsonPath('status', true);

    expect(StudentAttendance::where('student_id', $student['student_id'])->exists())->toBeTrue();
});

test('a teacher not assigned to the section is rejected with 403', function () {
    $teacherUser = AcademicFixtures::teacher(subjectId: 1, sectionId: 1);
    $student = qrStudentInSection(1, 1, 1, 'QR-NOT-MINE');

    // A second section the teacher has no assignment to.
    DB::table('sections')->updateOrInsert(
        ['id' => 5],
        ['institute_id' => 1, 'branch_id' => 1, 'class_id' => 1, 'student_group_id' => 1, 'section_name' => 'Other Section'],
    );

    $this->actingAs($teacherUser)->postJson('/teacher/qr-attendance/scan', [
        'class_id' => 1,
        'section_id' => 5,
        'qr_code' => 'QR-NOT-MINE',
    ])->assertStatus(403);

    expect(StudentAttendance::where('student_id', $student['student_id'])->exists())->toBeFalse();
});

test('a homeroom (class) teacher can scan without a subject assignment', function () {
    $teacherUser = AcademicFixtures::teacher(subjectId: 1, sectionId: 1);
    $teacherId = DB::table('teachers')->where('user_id', $teacherUser->id)->value('id');

    // Make them the class teacher of section 2, where they teach no subject.
    DB::table('sections')->updateOrInsert(
        ['id' => 6],
        ['institute_id' => 1, 'branch_id' => 1, 'class_id' => 1, 'student_group_id' => 1, 'section_name' => 'Homeroom Section', 'class_teacher_id' => $teacherId],
    );
    $student = qrStudentInSection(1, 1, 6, 'QR-HOMEROOM');

    $this->actingAs($teacherUser)->postJson('/teacher/qr-attendance/scan', [
        'class_id' => 1,
        'section_id' => 6,
        'qr_code' => 'QR-HOMEROOM',
    ])->assertOk()->assertJsonPath('status', true);

    expect(StudentAttendance::where('student_id', $student['student_id'])->exists())->toBeTrue();
});

test('a non-teacher web session is blocked from the teacher scan route', function () {
    $admin = AcademicFixtures::user('Admin');

    $this->actingAs($admin)->postJson('/teacher/qr-attendance/scan', [
        'class_id' => 1, 'section_id' => 1, 'qr_code' => 'irrelevant',
    ])->assertStatus(403);
});

test('an unauthenticated request to the teacher QR routes is rejected', function () {
    // postJson() sends Accept: application/json, so even the web guard route
    // returns 401 JSON here rather than redirecting to a login page.
    $this->postJson('/teacher/qr-attendance/scan', ['class_id' => 1, 'section_id' => 1, 'qr_code' => 'x'])
        ->assertStatus(401);
    $this->postJson('/api/teacher/qr-attendance/scan', ['class_id' => 1, 'section_id' => 1, 'qr_code' => 'x'])
        ->assertStatus(401);
});

// ── API ──────────────────────────────────────────────────────────────────

test('the API sections endpoint only returns sections the teacher owns', function () {
    $teacherUser = AcademicFixtures::teacher(subjectId: 1, sectionId: 1);
    Passport::actingAs($teacherUser, [], 'api');

    $response = $this->getJson('/api/teacher/qr-attendance/sections')->assertOk();

    $sectionIds = collect($response->json('data'))->pluck('section_id');
    expect($sectionIds->all())->toBe([1]);
});

test('the API scan endpoint marks attendance for an owned section', function () {
    $teacherUser = AcademicFixtures::teacher(subjectId: 1, sectionId: 1);
    $student = qrStudentInSection(1, 1, 1, 'QR-API-OK');
    Passport::actingAs($teacherUser, [], 'api');

    $this->postJson('/api/teacher/qr-attendance/scan', [
        'class_id' => 1,
        'section_id' => 1,
        'qr_code' => 'QR-API-OK',
    ])->assertOk()->assertJsonPath('status', true);

    expect(StudentAttendance::where('student_id', $student['student_id'])->exists())->toBeTrue();
});

test('the API scan endpoint rejects a student outside the requested section', function () {
    $teacherUser = AcademicFixtures::teacher(subjectId: 1, sectionId: 1);
    // Student actually belongs to tenant-2's class/section (a different tenant, so also institute-scoped away).
    $otherStudent = qrStudentInSection(2, 2, 2, 'QR-API-CROSS');
    Passport::actingAs($teacherUser, [], 'api');

    $this->postJson('/api/teacher/qr-attendance/scan', [
        'class_id' => 1,
        'section_id' => 1,
        'qr_code' => 'QR-API-CROSS',
    ])->assertStatus(404); // not found: institute-scoped lookup never sees tenant-2's QR code

    expect(StudentAttendance::where('student_id', $otherStudent['student_id'])->exists())->toBeFalse();
});

// ── Check-in / check-out through the full HTTP flow ──────────────────────

test('the first API scan checks in and returns check_in_at with no check_out_at', function () {
    $teacherUser = AcademicFixtures::teacher(subjectId: 1, sectionId: 1);
    qrStudentInSection(1, 1, 1, 'QR-API-CHECKIN');
    Passport::actingAs($teacherUser, [], 'api');

    $this->postJson('/api/teacher/qr-attendance/scan', [
        'class_id' => 1, 'section_id' => 1, 'qr_code' => 'QR-API-CHECKIN',
    ])->assertOk()
        ->assertJsonPath('data.status', 'check_in')
        ->assertJsonPath('data.check_out_at', null);
});

test('the second API scan checks out on the same row, and a third reports already checked out', function () {
    $teacherUser = AcademicFixtures::teacher(subjectId: 1, sectionId: 1);
    $student = qrStudentInSection(1, 1, 1, 'QR-API-CHECKOUT');
    Passport::actingAs($teacherUser, [], 'api');

    $this->postJson('/api/teacher/qr-attendance/scan', ['class_id' => 1, 'section_id' => 1, 'qr_code' => 'QR-API-CHECKOUT'])->assertOk();

    $second = $this->postJson('/api/teacher/qr-attendance/scan', ['class_id' => 1, 'section_id' => 1, 'qr_code' => 'QR-API-CHECKOUT'])
        ->assertOk()
        ->assertJsonPath('data.status', 'check_out');
    $checkOutAt = $second->json('data.check_out_at');

    $this->postJson('/api/teacher/qr-attendance/scan', ['class_id' => 1, 'section_id' => 1, 'qr_code' => 'QR-API-CHECKOUT'])
        ->assertOk()
        ->assertJsonPath('data.status', 'already_checked_out')
        ->assertJsonPath('data.check_out_at', $checkOutAt); // unchanged by the third scan

    expect(StudentAttendance::where('student_id', $student['student_id'])->count())->toBe(1);
});
