<?php

use App\Services\AdmissionNumberService;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Passport;
use Modules\Authentication\Models\Institute;
use Tests\Support\AcademicFixtures;

/*
|--------------------------------------------------------------------------
| Academic system — Phase 7: institute-wise admission number generation.
|--------------------------------------------------------------------------
*/

beforeEach(fn () => AcademicFixtures::build());

function studentPayload(array $overrides = []): array
{
    static $n = 0;
    $n++;

    return array_merge([
        'first_name' => 'Auto', 'last_name' => 'Student',
        'class_id' => 1, 'section_id' => 1, 'roll' => 100 + $n,
        'email' => "auto{$n}@test.local", 'phone' => '017'.str_pad((string) (10000000 + $n), 8, '0', STR_PAD_LEFT),
        'password' => 'secret123', 'password_confirmation' => 'secret123',
    ], $overrides);
}

// ── Service-level ────────────────────────────────────────────────────────

test('the sequence starts at 000001 and increments per institute', function () {
    $institute = Institute::findOrFail(1);
    $service = app(AdmissionNumberService::class);

    expect($service->next($institute))->toBe('000001')
        ->and($service->next($institute))->toBe('000002')
        ->and($service->next($institute))->toBe('000003');
});

test('two institutes have completely independent sequences', function () {
    $service = app(AdmissionNumberService::class);

    expect($service->next(Institute::findOrFail(1)))->toBe('000001');
    expect($service->next(Institute::findOrFail(2)))->toBe('000001');
    expect($service->next(Institute::findOrFail(1)))->toBe('000002');
});

test('a configured prefix is applied to generated numbers', function () {
    $institute = Institute::findOrFail(1);
    $institute->update(['admission_no_prefix' => 'MS-']);

    expect(app(AdmissionNumberService::class)->next($institute))->toBe('MS-000001');
});

test('generation skips a number already taken by an existing manual admission number', function () {
    $institute = Institute::findOrFail(1);
    $userId = DB::table('users')->insertGetId([
        'institute_id' => 1, 'branch_id' => 1, 'name' => 'Manual', 'phone' => '01700000099',
        'email' => 'manual-reg@test.local', 'password' => bcrypt('x'), 'user_type' => 'Student', 'status' => 1,
    ]);
    DB::table('students')->insert([
        'institute_id' => 1, 'branch_id' => 1, 'user_id' => $userId, 'first_name' => 'Manual',
        'phone' => '01700000099', 'registration_no' => '000001', 'status' => '1',
    ]);

    expect(app(AdmissionNumberService::class)->next($institute))->toBe('000002');
});

test('manual mode leaves the caller-supplied number untouched', function () {
    $institute = Institute::findOrFail(1); // default mode is manual
    expect(app(AdmissionNumberService::class)->resolve($institute, 'CUSTOM-42'))->toBe('CUSTOM-42')
        ->and(app(AdmissionNumberService::class)->resolve($institute, null))->toBeNull();
});

// ── Settings API ─────────────────────────────────────────────────────────

test('admission number settings default to manual and can be switched to auto with a prefix', function () {
    Passport::actingAs(AcademicFixtures::user('Admin'), [], 'api');

    $this->getJson('/api/admission-number-settings')->assertOk()->assertJsonPath('data.mode', 'manual');

    $this->putJson('/api/admission-number-settings', ['mode' => 'auto', 'prefix' => 'MS-'])
        ->assertOk()->assertJsonPath('data.mode', 'auto')->assertJsonPath('data.prefix', 'MS-');

    expect(Institute::findOrFail(1)->admission_no_mode)->toBe('auto');
});

test('updating admission number settings requires permission', function () {
    Passport::actingAs(AcademicFixtures::user('Staff'), [], 'api');

    $this->putJson('/api/admission-number-settings', ['mode' => 'auto'])->assertStatus(403);
    $this->getJson('/api/admission-number-settings')->assertOk(); // read is unrestricted to any authenticated staff
});

test('an invalid prefix or mode is rejected', function () {
    Passport::actingAs(AcademicFixtures::user('Admin'), [], 'api');

    $this->putJson('/api/admission-number-settings', ['mode' => 'sometimes'])->assertStatus(422);
    $this->putJson('/api/admission-number-settings', ['mode' => 'auto', 'prefix' => '<script>'])->assertStatus(422);
});

// ── End-to-end student creation ─────────────────────────────────────────

test('in manual mode a student is created with the admin-supplied admission number', function () {
    Passport::actingAs(AcademicFixtures::user('Admin'), [], 'api');

    $this->postJson('/api/students', studentPayload(['registration_no' => 'MAN-001']))->assertOk();

    expect(DB::table('students')->where('registration_no', 'MAN-001')->exists())->toBeTrue();
});

test('in auto mode admission numbers are generated sequentially and any client value is ignored', function () {
    Institute::findOrFail(1)->update(['admission_no_mode' => 'auto']);
    Passport::actingAs(AcademicFixtures::user('Admin'), [], 'api');

    $this->postJson('/api/students', studentPayload(['registration_no' => 'ignored-value']))->assertOk();
    $this->postJson('/api/students', studentPayload())->assertOk();

    $numbers = DB::table('students')->where('institute_id', 1)->orderBy('id')->pluck('registration_no');
    expect($numbers->all())->toBe(['000001', '000002']);
});

test('switching to auto mode never collides with, or changes, admission numbers issued manually before the switch', function () {
    Passport::actingAs(AcademicFixtures::user('Admin'), [], 'api');
    $this->postJson('/api/students', studentPayload(['registration_no' => '000001']))->assertOk();

    Institute::findOrFail(1)->update(['admission_no_mode' => 'auto']);
    $this->postJson('/api/students', studentPayload())->assertOk();

    $numbers = DB::table('students')->where('institute_id', 1)->orderBy('id')->pluck('registration_no');
    expect($numbers->all())->toBe(['000001', '000002']); // manual kept, auto skipped past it
});

test('a duplicate manually-entered admission number is rejected within the same institute', function () {
    Passport::actingAs(AcademicFixtures::user('Admin'), [], 'api');
    $this->postJson('/api/students', studentPayload(['registration_no' => 'DUP-1']))->assertOk();

    $this->postJson('/api/students', studentPayload(['registration_no' => 'DUP-1']))
        ->assertStatus(422)->assertJsonValidationErrors('registration_no');

    expect(DB::table('students')->where('registration_no', 'DUP-1')->count())->toBe(1);
});

test('the same admission number is allowed in a different institute', function () {
    Passport::actingAs(AcademicFixtures::user('Admin', 1), [], 'api');
    $this->postJson('/api/students', studentPayload(['registration_no' => 'SHARED-1']))->assertOk();

    Passport::actingAs(AcademicFixtures::user('Admin', 2), [], 'api');
    $this->postJson('/api/students', studentPayload(['registration_no' => 'SHARED-1', 'class_id' => 2, 'section_id' => 2]))->assertOk();

    expect(DB::table('students')->where('registration_no', 'SHARED-1')->count())->toBe(2);
});
