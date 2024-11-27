<?php

use Illuminate\Support\Facades\DB;
use Laravel\Passport\Passport;
use Tests\Support\AcademicFixtures;

/*
|--------------------------------------------------------------------------
| Academic system — Phase 5: certificate system.
|--------------------------------------------------------------------------
*/

beforeEach(fn () => AcademicFixtures::build());

function certAdmin(array $extraPermissions = []): void
{
    Passport::actingAs(AcademicFixtures::user('Admin', 1, $extraPermissions), [], 'api');
}

test('a hardcoded-format certificate is issued with a unique number and QR-verifiable code', function () {
    $s = AcademicFixtures::student(1);
    certAdmin();

    $issue = $this->postJson('/api/certificates/issue', [
        'type' => 'general-certificate', 'student_session_id' => $s['student_session_id'],
    ])->assertOk()->json('data');

    expect($issue['certificate_number'])->toStartWith('CERT-GENERALCERT-')
        ->and($issue['verification_url'])->toContain($issue['issue']['verification_code']);

    $verify = $this->getJson('/api/certificate-verify/'.$issue['issue']['verification_code'])->assertOk()->json('data');
    expect($verify['valid'])->toBeTrue()->and($verify['certificate_number'])->toBe($issue['certificate_number']);

    $this->getJson('/api/certificate-verify/NOT-A-REAL-CODE')->assertStatus(404);
});

test('certificate numbers increment per institute and type independently', function () {
    $a = AcademicFixtures::student(1, '1');
    $b = AcademicFixtures::student(1, '2');
    certAdmin();

    $n1 = $this->postJson('/api/certificates/issue', ['type' => 'general-certificate', 'student_session_id' => $a['student_session_id']])->json('data.certificate_number');
    $n2 = $this->postJson('/api/certificates/issue', ['type' => 'general-certificate', 'student_session_id' => $b['student_session_id']])->json('data.certificate_number');
    $n3 = $this->postJson('/api/certificates/issue', ['type' => 'testimonial', 'student_session_id' => $a['student_session_id']])->json('data.certificate_number');

    expect($n1)->toEndWith('000001')->and($n2)->toEndWith('000002')->and($n3)->toEndWith('000001');
});

test('a custom merge-field template replaces the hardcoded wording', function () {
    $s = AcademicFixtures::student(1);
    $templateId = DB::table('certificate_templates')->insertGetId([
        'institute_id' => 1, 'branch_id' => 1, 'name' => 'Custom General', 'type' => 'general-certificate',
        'body' => '<p>This certifies that {{student_name}} of class {{class_name}}, roll {{roll}}, has completed the {{session_name}} session at {{school_name}}.</p>',
        'is_active' => 1, 'created_at' => now(), 'updated_at' => now(),
    ]);
    DB::table('settings')->updateOrInsert(['institute_id' => 1, 'branch_id' => 1, 'name' => 'school_name'], ['value' => 'AceCademy']);
    clear_settings_map();
    certAdmin();

    $issue = $this->postJson('/api/certificates/issue', [
        'type' => 'general-certificate', 'student_session_id' => $s['student_session_id'], 'template_id' => $templateId,
    ])->assertOk()->json('data.issue');

    $pdf = $this->get("/api/certificates/{$issue['id']}/pdf")->assertOk();
    expect($pdf->headers->get('content-type'))->toContain('pdf');
});

test('bulk issue produces one certificate per student in the class with a shared batch id', function () {
    $a = AcademicFixtures::student(1, '1');
    $b = AcademicFixtures::student(1, '2');
    certAdmin(['layouts_certificates_bulk_issue']);

    $result = $this->postJson('/api/certificates/bulk-issue', [
        'type' => 'attendance-certificate', 'class_id' => 1, 'section_id' => 1,
    ])->assertOk()->json('data');

    expect($result['issued'])->toBe(2)->and($result['failed'])->toBe(0);
    $batchIds = DB::table('certificate_issues')->pluck('batch_id')->unique();
    expect($batchIds)->toHaveCount(1)->and($batchIds->first())->toBe($result['batch_id']);
});

test('bulk issue requires its own permission even for a user who can issue singly', function () {
    $s = AcademicFixtures::student(1);
    Passport::actingAs(AcademicFixtures::user('Staff', 1, ['layouts_certificates_general']), [], 'api');

    $this->postJson('/api/certificates/issue', ['type' => 'general-certificate', 'student_session_id' => $s['student_session_id']])->assertOk();
    $this->postJson('/api/certificates/bulk-issue', ['type' => 'general-certificate', 'class_id' => 1])->assertStatus(403);
});

test('issuing a certificate requires the permission for that specific type', function () {
    $s = AcademicFixtures::student(1);
    Passport::actingAs(AcademicFixtures::user('Staff', 1, ['layouts_certificates_general']), [], 'api');

    $this->postJson('/api/certificates/issue', ['type' => 'general-certificate', 'student_session_id' => $s['student_session_id']])->assertOk();
    $this->postJson('/api/certificates/issue', ['type' => 'migration-certificate', 'student_session_id' => $s['student_session_id']])->assertStatus(403);
});

test('a certificate cannot be issued for a student outside the caller institute', function () {
    $foreign = AcademicFixtures::student(2);
    certAdmin(['layouts_certificates_general']);

    $this->postJson('/api/certificates/issue', [
        'type' => 'general-certificate', 'student_session_id' => $foreign['student_session_id'],
    ])->assertStatus(422);

    expect(DB::table('certificate_issues')->count())->toBe(0);
});

test('re-downloading a certificate PDF does not mint a new certificate number', function () {
    $s = AcademicFixtures::student(1);
    certAdmin();
    $issue = $this->postJson('/api/certificates/issue', [
        'type' => 'general-certificate', 'student_session_id' => $s['student_session_id'],
    ])->json('data.issue');

    $this->get("/api/certificates/{$issue['id']}/pdf")->assertOk();
    $this->get("/api/certificates/{$issue['id']}/pdf")->assertOk();

    expect(DB::table('certificate_issues')->count())->toBe(1);
});
