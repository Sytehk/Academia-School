<?php

use Illuminate\Support\Facades\DB;
use Laravel\Passport\Passport;
use Tests\Support\AcademicFixtures;

/*
|--------------------------------------------------------------------------
| Phase 6 — Student self-service QR display.
|--------------------------------------------------------------------------
| The student can only ever view their OWN code (no id parameter anywhere in
| these routes), so there is no impersonation surface to test against — it's
| architecturally impossible to request another student's code through them.
*/

beforeEach(fn () => AcademicFixtures::build());

test('a student sees their own QR code on the web dashboard page', function () {
    $student = AcademicFixtures::student(1);
    DB::table('student_sessions')->where('id', $student['student_session_id'])->update(['qr_code' => 'QR-WEB-DISPLAY']);

    $response = $this->actingAs($student['user'])->get('/student/qr-attendance');

    $response->assertOk();
    $response->assertSee('idc-qr', false);
    $response->assertSee('data:image/svg+xml;base64,', false);
});

test('the web page handles a student with no QR code yet without erroring', function () {
    $student = AcademicFixtures::student(1);
    // qr_code left null (AcademicFixtures::student() never sets it).

    $this->actingAs($student['user'])->get('/student/qr-attendance')->assertOk();
});

test('the API returns the authenticated student\'s own qr_code, never another student\'s', function () {
    $studentA = AcademicFixtures::student(1, '1');
    $studentB = AcademicFixtures::student(1, '2');
    DB::table('student_sessions')->where('id', $studentA['student_session_id'])->update(['qr_code' => 'QR-A']);
    DB::table('student_sessions')->where('id', $studentB['student_session_id'])->update(['qr_code' => 'QR-B']);

    Passport::actingAs($studentA['user'], [], 'api');

    $this->getJson('/api/student/qr-attendance')
        ->assertOk()
        ->assertJsonPath('data.qr_code', 'QR-A');
});

test('a non-student is blocked from the student QR routes', function () {
    $admin = AcademicFixtures::user('Admin');

    $this->actingAs($admin)->get('/student/qr-attendance')->assertStatus(403);
});

test('an unauthenticated request to the student QR routes is rejected', function () {
    $this->getJson('/api/student/qr-attendance')->assertStatus(401);
});
