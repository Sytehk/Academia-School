<?php

use Illuminate\Support\Facades\DB;
use Laravel\Passport\Passport;
use Modules\Authentication\Models\Institute;
use Modules\Elearning\Models\Course;
use Modules\Elearning\Models\Enrollment;
use Modules\Frontend\Models\AdmissionForm;
use Tests\Support\AcademicFixtures;

/*
|--------------------------------------------------------------------------
| Phase 1 regression fixes — currency helper, course enrollment amount,
| admin enrollment list filter, admission-approval number generation.
|--------------------------------------------------------------------------
| Found during the currency/QR/course/admission-number audit (see
| CURRENCY_QR_COURSE_ADMISSION_AUDIT_PHASE1.md). These are bugs in shipped
| code, not new features, so they're covered independently of the wider
| feature work.
*/

beforeEach(fn () => AcademicFixtures::build());

function createTestCourse(array $overrides = []): Course
{
    static $n = 0;
    $n++;

    $categoryId = DB::table('course_categories')->insertGetId([
        'institute_id' => 1, 'name' => 'Category', 'slug' => 'category-'.uniqid().$n,
        'created_at' => now(), 'updated_at' => now(),
    ]);

    // Course::create() drops branch_id (not in $fillable — normally auto-stamped by
    // BelongsToTenant on an authenticated request); forceFill so fixtures work
    // regardless of whether a user is authenticated yet.
    $course = new Course;
    $course->forceFill(array_merge([
        'institute_id' => 1,
        'branch_id' => 1,
        'course_category_id' => $categoryId,
        'title' => "Test Course {$n}",
        'slug' => "test-course-{$n}-".uniqid(),
        'regular_price' => 500,
        'offer_price' => 350,
        'status' => 'published',
    ], $overrides));
    $course->save();

    return $course;
}

// ── format_currency() ───────────────────────────────────────────────────

test('format_currency uses the institute currency_symbol setting instead of a hardcoded dollar sign', function () {
    DB::table('settings')->updateOrInsert(
        ['institute_id' => 1, 'branch_id' => 1, 'name' => 'currency_symbol'],
        ['value' => '৳'],
    );
    clear_settings_map();

    $this->actingAs(AcademicFixtures::user('Admin'));

    expect(format_currency(1000))->toBe('৳1,000.00');
});

test('format_currency falls back to a dollar sign when no currency_symbol setting exists', function () {
    DB::table('settings')->where(['institute_id' => 1, 'branch_id' => 1, 'name' => 'currency_symbol'])->delete();
    clear_settings_map();

    $this->actingAs(AcademicFixtures::user('Admin'));

    expect(format_currency(500))->toBe('$500.00');
});

// ── Course enrollment amount_paid ───────────────────────────────────────

test('one-time course enrollment records amount_paid from the course offer price', function () {
    $course = createTestCourse(['regular_price' => 500, 'offer_price' => 350]);
    $student = AcademicFixtures::student(1);

    Passport::actingAs($student['user'], [], 'api');

    $this->postJson('/api/enrollment-course', [
        'course_id' => $course->id,
        'enrollment_type' => 'one_time',
    ])->assertOk();

    $enrollment = Enrollment::where('user_id', $student['user']->id)->where('course_id', $course->id)->first();
    expect((float) $enrollment->amount_paid)->toBe(350.0);
});

test('a free course (offer_price 0) records amount_paid as 0, never falling back to the regular price', function () {
    $course = createTestCourse(['regular_price' => 500, 'offer_price' => 0]);
    $student = AcademicFixtures::student(1);

    Passport::actingAs($student['user'], [], 'api');

    $this->postJson('/api/enrollment-course', [
        'course_id' => $course->id,
        'enrollment_type' => 'one_time',
    ])->assertOk();

    $enrollment = Enrollment::where('user_id', $student['user']->id)->where('course_id', $course->id)->first();
    expect((float) $enrollment->amount_paid)->toBe(0.0);
});

// ── Admin enrollment list ───────────────────────────────────────────────

test('the admin enrollment list can filter by student without a SQL error', function () {
    $course = createTestCourse();
    $student = AcademicFixtures::student(1);

    Enrollment::create([
        'institute_id' => 1,
        'user_id' => $student['user']->id,
        'course_id' => $course->id,
        'enrollment_type' => 'one_time',
        'enrollment_date' => now(),
        'amount_paid' => 100,
        'status' => 'active',
    ]);

    Passport::actingAs(AcademicFixtures::user('Admin'), [], 'api');

    $this->getJson('/api/enrollments/list?student_id='.$student['user']->id)
        ->assertOk()
        ->assertJsonPath('data.enrollments.0.student_id', $student['user']->id);
});

// ── Admission-approval admission-number wiring ──────────────────────────

test('approving an admission form in auto mode assigns a generated admission number', function () {
    Institute::findOrFail(1)->update(['admission_no_mode' => 'auto']);
    $admin = AcademicFixtures::user('Admin');

    $admission = AdmissionForm::create([
        'institute_id' => 1,
        'branch_id' => 1,
        'class_id' => 1,
        'first_name' => 'Applicant',
        'phone' => '01700001234',
        'registration_no' => 'whatever-the-applicant-typed',
        'status' => 'Pending',
    ]);

    $this->actingAs($admin)->post("/admission-forms/{$admission->id}/approve");

    $studentNumber = DB::table('students')->where('phone', '01700001234')->value('registration_no');
    expect($studentNumber)->toBe('000001');
});

test('approving an admission form in manual mode preserves the applicant-supplied admission number', function () {
    // Default admission_no_mode is manual.
    $admin = AcademicFixtures::user('Admin');

    $admission = AdmissionForm::create([
        'institute_id' => 1,
        'branch_id' => 1,
        'class_id' => 1,
        'first_name' => 'Applicant',
        'phone' => '01700005678',
        'registration_no' => 'APP-42',
        'status' => 'Pending',
    ]);

    $this->actingAs($admin)->post("/admission-forms/{$admission->id}/approve");

    $studentNumber = DB::table('students')->where('phone', '01700005678')->value('registration_no');
    expect($studentNumber)->toBe('APP-42');
});

test('approving two admission forms in auto mode never assigns the same admission number', function () {
    Institute::findOrFail(1)->update(['admission_no_mode' => 'auto']);
    $admin = AcademicFixtures::user('Admin');

    $first = AdmissionForm::create([
        'institute_id' => 1, 'branch_id' => 1, 'class_id' => 1,
        'first_name' => 'First', 'phone' => '01700009001', 'status' => 'Pending',
    ]);
    $second = AdmissionForm::create([
        'institute_id' => 1, 'branch_id' => 1, 'class_id' => 1,
        'first_name' => 'Second', 'phone' => '01700009002', 'status' => 'Pending',
    ]);

    $this->actingAs($admin)->post("/admission-forms/{$first->id}/approve");
    $this->actingAs($admin)->post("/admission-forms/{$second->id}/approve");

    $numbers = DB::table('students')->whereIn('phone', ['01700009001', '01700009002'])->pluck('registration_no');
    expect($numbers->unique()->count())->toBe(2)
        ->and($numbers->sort()->values()->all())->toBe(['000001', '000002']);
});
