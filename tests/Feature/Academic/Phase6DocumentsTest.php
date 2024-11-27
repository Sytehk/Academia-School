<?php

use Illuminate\Support\Facades\DB;
use Laravel\Passport\Passport;
use Tests\Support\AcademicFixtures;

/*
|--------------------------------------------------------------------------
| Academic system — Phase 6: academic documents (ID cards, transcript).
|--------------------------------------------------------------------------
*/

beforeEach(fn () => AcademicFixtures::build());

test('a student ID card PDF renders for a class with the id_card_student permission', function () {
    AcademicFixtures::student(1, '1');
    AcademicFixtures::student(1, '2');
    Passport::actingAs(AcademicFixtures::user('Staff', 1, ['id_card_student']), [], 'api');

    $pdf = $this->get('/api/id-cards/students/pdf?class_id=1&section_id=1')->assertOk();
    expect($pdf->headers->get('content-type'))->toContain('pdf');
});

test('the student ID card route requires its own permission, distinct from teacher/staff', function () {
    AcademicFixtures::student(1);
    Passport::actingAs(AcademicFixtures::user('Staff', 1, ['id_card_teacher']), [], 'api');

    $this->get('/api/id-cards/students/pdf?class_id=1&section_id=1')->assertStatus(403);
});

test('a teacher ID card PDF renders', function () {
    $userId = DB::table('users')->insertGetId([
        'institute_id' => 1, 'branch_id' => 1, 'name' => 'Teacher One', 'phone' => '01711112222',
        'email' => 'teacher.idcard@test.local', 'password' => bcrypt('x'), 'user_type' => 'Teacher', 'status' => 1,
    ]);
    DB::table('teachers')->insert(['institute_id' => 1, 'branch_id' => 1, 'user_id' => $userId, 'name' => 'Teacher One', 'gender' => 'Male', 'phone' => '01711112222', 'status' => 1]);
    Passport::actingAs(AcademicFixtures::user('Staff', 1, ['id_card_teacher']), [], 'api');

    $pdf = $this->get('/api/id-cards/teachers/pdf')->assertOk();
    expect($pdf->headers->get('content-type'))->toContain('pdf');
});

test('a staff ID card PDF renders', function () {
    $userId = DB::table('users')->insertGetId([
        'institute_id' => 1, 'branch_id' => 1, 'name' => 'Staff One', 'phone' => '01711113333',
        'email' => 'staff.idcard@test.local', 'password' => bcrypt('x'), 'user_type' => 'Staff', 'status' => 1,
    ]);
    DB::table('staffs')->insert(['institute_id' => 1, 'branch_id' => 1, 'user_id' => $userId, 'name' => 'Staff One', 'gender' => 'Male', 'phone' => '01711113333', 'status' => 1]);
    Passport::actingAs(AcademicFixtures::user('Staff', 1, ['id_card_staff']), [], 'api');

    $pdf = $this->get('/api/id-cards/staff/pdf')->assertOk();
    expect($pdf->headers->get('content-type'))->toContain('pdf');
});

test('an academic transcript is issued, downloadable and QR-verifiable across sessions', function () {
    $student = AcademicFixtures::student(1);
    DB::table('exam_marks')->insert([
        'institute_id' => 1, 'branch_id' => 1, 'session_id' => 1, 'student_id' => $student['student_id'],
        'class_id' => 1, 'group_id' => 1, 'subject_id' => 1, 'exam_id' => 1,
        'total_marks' => 75, 'percentage' => 75, 'grade' => 'A', 'grade_point' => 5, 'status' => 'pass',
    ]);
    Passport::actingAs(AcademicFixtures::user('Admin'), [], 'api');

    $issue = $this->postJson("/api/students/{$student['student_id']}/transcript")->assertOk()->json('data');
    expect($issue['issue']['transcript_number'])->toStartWith('TR-')
        ->and($issue['verification_url'])->toContain($issue['issue']['verification_code']);

    $pdf = $this->get("/api/transcripts/{$issue['issue']['id']}/pdf")->assertOk();
    expect($pdf->headers->get('content-type'))->toContain('pdf');

    $verify = $this->getJson('/api/transcript-verify/'.$issue['issue']['verification_code'])->assertOk()->json('data');
    expect($verify['valid'])->toBeTrue()->and($verify['transcript_number'])->toBe($issue['issue']['transcript_number']);

    $this->getJson('/api/transcript-verify/BOGUS-CODE-0000')->assertStatus(404);
});

test('a transcript cannot be issued for another institute student', function () {
    $foreign = AcademicFixtures::student(2);
    Passport::actingAs(AcademicFixtures::user('Admin', 1), [], 'api');

    $this->postJson("/api/students/{$foreign['student_id']}/transcript")->assertStatus(404);
    expect(DB::table('transcript_issues')->count())->toBe(0);
});

test('transcript issuance requires its permission', function () {
    $student = AcademicFixtures::student(1);
    Passport::actingAs(AcademicFixtures::user('Staff'), [], 'api');

    $this->postJson("/api/students/{$student['student_id']}/transcript")->assertStatus(403);
});
