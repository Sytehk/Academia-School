<?php

use App\Services\QrAttendanceService;
use Illuminate\Support\Facades\DB;
use Modules\Academic\Models\StudentAttendance;
use Tests\Support\AcademicFixtures;

/*
|--------------------------------------------------------------------------
| Phase 6-7 / Check-In-Out — shared QR-scan-to-attendance service.
|--------------------------------------------------------------------------
| Extracted so the new Teacher scan flow doesn't duplicate the existing Admin
| implementation (Modules\Academic\Http\Controllers\{WEB\QrAttendanceController,
| API\APIAttendanceController}::studentQrCodeAttendance, both left untouched
| — check-in/check-out is scoped to this shared service only; see the Check-
| In/Check-Out feature report for why).
|
| Check-in/check-out state machine, layered onto the SAME student_attendances
| row (no separate table): 1st valid scan of the day -> check_in_at; 2nd ->
| check_out_at; 3rd+ -> no change, "already checked out".
*/

beforeEach(function () {
    AcademicFixtures::build();
    // student_attendances.period_id is a required FK; QR attendance hardcodes
    // period 1 (matching the existing Admin scan flow), which AcademicFixtures
    // doesn't seed itself (other tests seed their own periods with plain
    // insert(), so this uses updateOrInsert to stay collision-safe).
    DB::table('periods')->updateOrInsert(['id' => 1], ['institute_id' => 1, 'branch_id' => 1, 'period' => 'Period 1']);
});

function setStudentQrCode(int $studentSessionId, string $qrCode): void
{
    DB::table('student_sessions')->where('id', $studentSessionId)->update(['qr_code' => $qrCode]);
}

// ── Check-in ─────────────────────────────────────────────────────────────

test('the first valid scan of the day creates the attendance row and sets check_in_at', function () {
    $student = AcademicFixtures::student(1);
    setStudentQrCode($student['student_session_id'], 'QR-1');
    $this->actingAs($student['user']); // resolves get_institute_id()/get_branch_id() for the scan

    $result = app(QrAttendanceService::class)->scan('QR-1');

    expect($result['status'])->toBe(QrAttendanceService::STATUS_CHECKED_IN);
    expect($result['check_in_at'])->not->toBeNull();
    expect($result['check_out_at'])->toBeNull();

    $attendance = StudentAttendance::where('student_id', $student['student_id'])->first();
    expect($attendance->attendance)->toBe(1)
        ->and($attendance->check_in_at)->not->toBeNull()
        ->and($attendance->check_out_at)->toBeNull();
});

// ── Check-out ────────────────────────────────────────────────────────────

test('the second valid scan of the day sets check_out_at on the SAME row, leaving check_in_at unchanged', function () {
    $student = AcademicFixtures::student(1);
    setStudentQrCode($student['student_session_id'], 'QR-2');
    $this->actingAs($student['user']);
    $service = app(QrAttendanceService::class);

    $first = $service->scan('QR-2');
    $second = $service->scan('QR-2');

    expect($second['status'])->toBe(QrAttendanceService::STATUS_CHECKED_OUT);
    expect($second['check_in_at'])->toBe($first['check_in_at']); // untouched
    expect($second['check_out_at'])->not->toBeNull();

    expect(StudentAttendance::where('student_id', $student['student_id'])->count())->toBe(1); // same row, not a new one
});

// ── Repeated scan after checkout ─────────────────────────────────────────

test('a third scan after checkout changes nothing and reports already checked out', function () {
    $student = AcademicFixtures::student(1);
    setStudentQrCode($student['student_session_id'], 'QR-3');
    $this->actingAs($student['user']);
    $service = app(QrAttendanceService::class);

    $service->scan('QR-3');
    $second = $service->scan('QR-3');
    $third = $service->scan('QR-3');

    expect($third['status'])->toBe(QrAttendanceService::STATUS_ALREADY_CHECKED_OUT);
    expect($third['check_in_at'])->toBe($second['check_in_at']);
    expect($third['check_out_at'])->toBe($second['check_out_at']); // not overwritten

    expect(StudentAttendance::where('student_id', $student['student_id'])->count())->toBe(1);
});

// ── Existing (manual) attendance compatibility ──────────────────────────

test('a QR scan checks in against an attendance row that already exists from manual entry', function () {
    $student = AcademicFixtures::student(1);
    setStudentQrCode($student['student_session_id'], 'QR-MANUAL');
    $this->actingAs($student['user']);

    // Manual attendance already marked the student present today, with no
    // check-in/out (exactly how existing manual entry has always behaved).
    $manualRow = StudentAttendance::create([
        'institute_id' => 1, 'branch_id' => 1, 'student_id' => $student['student_id'],
        'class_id' => 1, 'section_id' => 1, 'period_id' => 1,
        'date' => now()->toDateString(), 'attendance' => 1,
    ]);
    expect($manualRow->check_in_at)->toBeNull();

    $result = app(QrAttendanceService::class)->scan('QR-MANUAL');

    expect($result['status'])->toBe(QrAttendanceService::STATUS_CHECKED_IN);
    expect(StudentAttendance::where('student_id', $student['student_id'])->count())->toBe(1); // still the same row
    expect($manualRow->fresh()->check_in_at)->not->toBeNull();
});

test('a historical attendance record is never modified by later QR scans', function () {
    $student = AcademicFixtures::student(1);
    setStudentQrCode($student['student_session_id'], 'QR-HIST');
    $this->actingAs($student['user']);

    $yesterday = StudentAttendance::create([
        'institute_id' => 1, 'branch_id' => 1, 'student_id' => $student['student_id'],
        'class_id' => 1, 'section_id' => 1, 'period_id' => 1,
        'date' => now()->subDay()->toDateString(), 'attendance' => 1,
    ]);

    app(QrAttendanceService::class)->scan('QR-HIST'); // today's scan

    expect($yesterday->fresh())
        ->check_in_at->toBeNull()
        ->check_out_at->toBeNull()
        ->attendance->toBe(1);
    // Today got its own, separate row.
    expect(StudentAttendance::where('student_id', $student['student_id'])->count())->toBe(2);
});

// ── Existing checks, unchanged behaviour ─────────────────────────────────

test('an unknown QR code is not found', function () {
    $admin = AcademicFixtures::user('Admin');
    $this->actingAs($admin);

    $result = app(QrAttendanceService::class)->scan('QR-DOES-NOT-EXIST');

    expect($result['status'])->toBe(QrAttendanceService::STATUS_NOT_FOUND);
});

test('a QR code belonging to another institute is not found (tenant scoping)', function () {
    $studentInTenant2 = AcademicFixtures::student(2);
    setStudentQrCode($studentInTenant2['student_session_id'], 'QR-CROSS-TENANT');

    // Scanning as a tenant-1 user must not resolve a tenant-2 student's QR code.
    $tenant1Admin = AcademicFixtures::user('Admin', 1);
    $this->actingAs($tenant1Admin);

    $result = app(QrAttendanceService::class)->scan('QR-CROSS-TENANT');

    expect($result['status'])->toBe(QrAttendanceService::STATUS_NOT_FOUND);
    expect(StudentAttendance::where('student_id', $studentInTenant2['student_id'])->exists())->toBeFalse();
});

test('an inactive student cannot be checked in via QR', function () {
    $student = AcademicFixtures::student(1);
    setStudentQrCode($student['student_session_id'], 'QR-INACTIVE');
    DB::table('students')->where('id', $student['student_id'])->update(['status' => '0']);
    $this->actingAs(AcademicFixtures::user('Admin'));

    $result = app(QrAttendanceService::class)->scan('QR-INACTIVE');

    expect($result['status'])->toBe(QrAttendanceService::STATUS_INACTIVE);
    expect(StudentAttendance::where('student_id', $student['student_id'])->exists())->toBeFalse();
});

test('scanning a student outside the expected class/section is rejected as a mismatch', function () {
    // Tenant 1 has class 1/section 1 by default (AcademicFixtures); put the student in class 1/section 1.
    $student = AcademicFixtures::student(1, '1', 1, 1);
    setStudentQrCode($student['student_session_id'], 'QR-MISMATCH');
    $this->actingAs(AcademicFixtures::user('Admin'));

    // Teacher opened attendance for a DIFFERENT class than the student's own.
    $result = app(QrAttendanceService::class)->scan('QR-MISMATCH', expectedClassId: 999, expectedSectionId: 1);

    expect($result['status'])->toBe(QrAttendanceService::STATUS_SECTION_MISMATCH);
    expect(StudentAttendance::where('student_id', $student['student_id'])->exists())->toBeFalse();
});

test('scanning a student inside the expected class/section checks them in', function () {
    $student = AcademicFixtures::student(1, '1', 1, 1);
    setStudentQrCode($student['student_session_id'], 'QR-MATCH');
    $this->actingAs(AcademicFixtures::user('Admin'));

    $result = app(QrAttendanceService::class)->scan('QR-MATCH', expectedClassId: 1, expectedSectionId: 1);

    expect($result['status'])->toBe(QrAttendanceService::STATUS_CHECKED_IN);
});
