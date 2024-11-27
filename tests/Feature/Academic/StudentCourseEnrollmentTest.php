<?php

use Illuminate\Support\Facades\DB;
use Laravel\Passport\Passport;
use Modules\Elearning\Models\Course;
use Modules\Elearning\Models\Enrollment;
use Tests\Support\AcademicFixtures;

/*
|--------------------------------------------------------------------------
| Phase 11 — Student Course Enrollment: browse, view, enroll.
|--------------------------------------------------------------------------
*/

beforeEach(fn () => AcademicFixtures::build());

function makeCourse(array $overrides = []): Course
{
    static $n = 0;
    $n++;

    $categoryId = DB::table('course_categories')->insertGetId([
        'institute_id' => 1, 'name' => 'Category', 'slug' => 'cat-'.uniqid().$n,
        'created_at' => now(), 'updated_at' => now(),
    ]);

    $course = new Course;
    $course->forceFill(array_merge([
        'institute_id' => 1,
        'branch_id' => 1,
        'course_category_id' => $categoryId,
        'title' => "Course {$n}",
        'slug' => "course-{$n}-".uniqid(),
        'regular_price' => 500,
        'offer_price' => 300,
        'status' => 'published',
    ], $overrides));
    $course->save();

    return $course;
}

// ── Available courses ────────────────────────────────────────────────────

test('a student only sees published courses from their own institute', function () {
    $published = makeCourse(['status' => 'published', 'title' => 'Published Course']);
    makeCourse(['status' => 'draft', 'title' => 'Draft Course']);
    makeCourse(['status' => 'private', 'title' => 'Private Course']);

    $student = AcademicFixtures::user('Student');
    Passport::actingAs($student, [], 'api');

    $response = $this->getJson('/api/student/courses')->assertOk();

    $titles = collect($response->json('data.data'))->pluck('title');
    expect($titles->all())->toBe(['Published Course']);
});

test('the available courses list flags which ones the student is already enrolled in', function () {
    $course = makeCourse();
    $student = AcademicFixtures::user('Student');
    Enrollment::create([
        'institute_id' => 1, 'user_id' => $student->id, 'course_id' => $course->id,
        'enrollment_type' => 'one_time', 'enrollment_date' => now(), 'amount_paid' => 300, 'status' => 'active',
    ]);

    Passport::actingAs($student, [], 'api');
    $response = $this->getJson('/api/student/courses')->assertOk();

    expect($response->json('data.data.0.is_enrolled'))->toBeTrue();
});

test('a course from another institute never appears in the available courses list', function () {
    makeCourse(['institute_id' => 2, 'branch_id' => 2, 'status' => 'published']);
    $student = AcademicFixtures::user('Student', 1);
    Passport::actingAs($student, [], 'api');

    $this->getJson('/api/student/courses')->assertOk()->assertJsonCount(0, 'data.data');
});

// ── Course details (pre-enrollment) ─────────────────────────────────────

test('a student can view details of a published course before enrolling', function () {
    $course = makeCourse();
    $student = AcademicFixtures::user('Student');
    Passport::actingAs($student, [], 'api');

    $this->getJson("/api/student/courses/{$course->id}")
        ->assertOk()
        ->assertJsonPath('data.is_enrolled', false);
});

test('a draft course is not visible via the course details endpoint', function () {
    $course = makeCourse(['status' => 'draft']);
    $student = AcademicFixtures::user('Student');
    Passport::actingAs($student, [], 'api');

    $this->getJson("/api/student/courses/{$course->id}")->assertStatus(404);
});

test('a student cannot fetch another institute\'s course details by direct id, even if published', function () {
    $course = makeCourse(['institute_id' => 2, 'branch_id' => 2, 'status' => 'published']);
    $student = AcademicFixtures::user('Student', 1);
    Passport::actingAs($student, [], 'api');

    $this->getJson("/api/student/courses/{$course->id}")->assertStatus(404);
});

// ── Enrollment: duplicate/invalid prevention ────────────────────────────

test('enrolling in a draft course is rejected', function () {
    $course = makeCourse(['status' => 'draft']);
    $student = AcademicFixtures::user('Student');
    Passport::actingAs($student, [], 'api');

    $this->postJson('/api/enrollment-course', ['course_id' => $course->id, 'enrollment_type' => 'one_time'])
        ->assertStatus(422);

    expect(Enrollment::where('user_id', $student->id)->where('course_id', $course->id)->exists())->toBeFalse();
});

test('enrolling in a published course succeeds and a second one-time enrollment is rejected', function () {
    $course = makeCourse(['status' => 'published']);
    $student = AcademicFixtures::user('Student');
    Passport::actingAs($student, [], 'api');

    $this->postJson('/api/enrollment-course', ['course_id' => $course->id, 'enrollment_type' => 'one_time'])->assertOk();
    $this->postJson('/api/enrollment-course', ['course_id' => $course->id, 'enrollment_type' => 'one_time'])->assertStatus(400);

    expect(Enrollment::where('user_id', $student->id)->where('course_id', $course->id)->count())->toBe(1);
});

// ── myCourseDetails: enrollment ownership + institute scoping ───────────

test('myCourseDetails is refused for a student not enrolled in the course', function () {
    $course = makeCourse();
    $student = AcademicFixtures::user('Student');
    Passport::actingAs($student, [], 'api');

    $this->getJson("/api/student/my-course-details/{$course->slug}")->assertStatus(403);
});

test('myCourseDetails succeeds for a student actually enrolled in the course', function () {
    $course = makeCourse();
    $student = AcademicFixtures::user('Student');
    Enrollment::create([
        'institute_id' => 1, 'user_id' => $student->id, 'course_id' => $course->id,
        'enrollment_type' => 'one_time', 'enrollment_date' => now(), 'amount_paid' => 300, 'status' => 'active',
    ]);
    Passport::actingAs($student, [], 'api');

    $this->getJson("/api/student/my-course-details/{$course->slug}")->assertOk();
});

test('myCourseDetails never leaks a course from another institute', function () {
    $course = makeCourse(['institute_id' => 2, 'branch_id' => 2]);
    // Same user id space is irrelevant — the student is in institute 1.
    $student = AcademicFixtures::user('Student', 1);
    Passport::actingAs($student, [], 'api');

    $this->getJson("/api/student/my-course-details/{$course->slug}")->assertStatus(404);
});

// ── Direct API access checks (not just UI visibility) ───────────────────

test('a non-student is blocked from the student course routes via direct API access', function () {
    $course = makeCourse();
    $admin = AcademicFixtures::user('Admin');
    Passport::actingAs($admin, [], 'api');

    $this->getJson('/api/student/courses')->assertStatus(403);
    $this->getJson("/api/student/courses/{$course->id}")->assertStatus(403);
    $this->postJson('/api/enrollment-course', ['course_id' => $course->id, 'enrollment_type' => 'one_time'])->assertStatus(403);
});

test('an unauthenticated request to the student course routes is rejected', function () {
    $course = makeCourse();

    $this->getJson('/api/student/courses')->assertStatus(401);
    $this->getJson("/api/student/courses/{$course->id}")->assertStatus(401);
    $this->postJson('/api/enrollment-course', ['course_id' => $course->id, 'enrollment_type' => 'one_time'])->assertStatus(401);
});
