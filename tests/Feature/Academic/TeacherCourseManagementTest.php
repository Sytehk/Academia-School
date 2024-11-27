<?php

use Illuminate\Support\Facades\DB;
use Laravel\Passport\Passport;
use Modules\Elearning\Models\Course;
use Modules\Elearning\Models\Enrollment;
use Tests\Support\AcademicFixtures;

/*
|--------------------------------------------------------------------------
| Phase 9-10 — Teacher Course CRUD + Enrollment Management.
|--------------------------------------------------------------------------
| Reuses the existing Course/Enrollment models, CourseStoreRequest/
| CourseUpdateRequest, and CourseRepository — the Admin course controller
| (Modules\Elearning\Http\Controllers\API\CourseController) is untouched.
*/

beforeEach(fn () => AcademicFixtures::build());

function courseCategoryId(int $institute = 1): int
{
    return DB::table('course_categories')->insertGetId([
        'institute_id' => $institute, 'name' => 'Category', 'slug' => 'category-'.uniqid(),
        'created_at' => now(), 'updated_at' => now(),
    ]);
}

function teacherCoursePayload(array $overrides = []): array
{
    static $n = 0;
    $n++;

    return array_merge([
        // CourseStoreRequest/CourseUpdateRequest validate this as a string.
        'course_category_id' => (string) courseCategoryId(),
        'title' => "Teacher Course {$n}",
        'slug' => "teacher-course-{$n}-".uniqid(),
        'status' => 'published',
        'type' => 'single',
        'payment_type' => 'one_time',
        'regular_price' => 500,
        'offer_price' => 300,
    ], $overrides);
}

/**
 * A teacher user + teachers row, with no assign_subjects membership — course
 * ownership doesn't depend on subject/section assignment, and AcademicFixtures
 * only seeds one subject/section per tenant, so calling AcademicFixtures::
 * teacher() more than once per tenant collides on assign_subjects' unique
 * (subject_id, section_id) constraint.
 */
function makeTeacherUser(int $institute = 1)
{
    $user = AcademicFixtures::user('Teacher', $institute);
    DB::table('teachers')->insert([
        'institute_id' => $institute, 'branch_id' => $institute, 'user_id' => $user->id, 'department_id' => null,
        'name' => 'Teacher', 'gender' => 'Male', 'phone' => $user->phone,
    ]);

    return $user;
}

// ── Teacher Course CRUD ──────────────────────────────────────────────────

test('a teacher can create a course and becomes its owner', function () {
    $teacher = makeTeacherUser();
    Passport::actingAs($teacher, [], 'api');

    $response = $this->postJson('/api/teacher/courses', teacherCoursePayload())->assertOk();

    $courseId = $response->json('data.id');
    expect(Course::withoutGlobalScopes()->find($courseId)->created_by)->toBe($teacher->id);
    expect(Course::withoutGlobalScopes()->find($courseId)->institute_id)->toBe(1);
});

test('a teacher only sees courses they created, not another teacher\'s', function () {
    $teacherA = makeTeacherUser();
    $teacherB = makeTeacherUser();

    Passport::actingAs($teacherA, [], 'api');
    $this->postJson('/api/teacher/courses', teacherCoursePayload(['title' => 'Course A']))->assertOk();

    Passport::actingAs($teacherB, [], 'api');
    $this->postJson('/api/teacher/courses', teacherCoursePayload(['title' => 'Course B']))->assertOk();

    Passport::actingAs($teacherA, [], 'api');
    $response = $this->getJson('/api/teacher/courses')->assertOk();

    $titles = collect($response->json('data.data'))->pluck('title');
    expect($titles->all())->toBe(['Course A']);
});

test('a teacher cannot view, update, or delete another teacher\'s course', function () {
    $teacherA = makeTeacherUser();
    $teacherB = makeTeacherUser();

    Passport::actingAs($teacherA, [], 'api');
    $courseId = $this->postJson('/api/teacher/courses', teacherCoursePayload())->json('data.id');

    Passport::actingAs($teacherB, [], 'api');
    $this->getJson("/api/teacher/courses/{$courseId}")->assertStatus(404);
    $this->putJson("/api/teacher/courses/{$courseId}", teacherCoursePayload())->assertStatus(404);
    $this->deleteJson("/api/teacher/courses/{$courseId}")->assertStatus(404);

    expect(Course::withoutGlobalScopes()->find($courseId)->title)->not->toBeNull();
});

test('a teacher can update and delete their own course', function () {
    $teacher = makeTeacherUser();
    Passport::actingAs($teacher, [], 'api');

    $courseId = $this->postJson('/api/teacher/courses', teacherCoursePayload())->json('data.id');

    $this->putJson("/api/teacher/courses/{$courseId}", teacherCoursePayload(['title' => 'Updated Title']))
        ->assertOk()->assertJsonPath('data.title', 'Updated Title');

    $this->deleteJson("/api/teacher/courses/{$courseId}")->assertOk();
    // Course uses SoftDeletes, so the row itself still exists — just marked deleted.
    expect(Course::withoutGlobalScopes()->find($courseId)->deleted_at)->not->toBeNull();
});

test('a teacher cannot delete their own course while it has enrollments', function () {
    $teacher = makeTeacherUser();
    Passport::actingAs($teacher, [], 'api');
    $courseId = $this->postJson('/api/teacher/courses', teacherCoursePayload())->json('data.id');

    Enrollment::create([
        'institute_id' => 1, 'user_id' => AcademicFixtures::user('Student')->id, 'course_id' => $courseId,
        'enrollment_type' => 'one_time', 'enrollment_date' => now(), 'amount_paid' => 300, 'status' => 'active',
    ]);

    $this->deleteJson("/api/teacher/courses/{$courseId}")->assertStatus(409);
    expect(Course::withoutGlobalScopes()->find($courseId))->not->toBeNull();
});

test('a course created by a teacher in institute 1 is invisible to a teacher in institute 2', function () {
    $teacher1 = makeTeacherUser();
    Passport::actingAs($teacher1, [], 'api');
    $courseId = $this->postJson('/api/teacher/courses', teacherCoursePayload())->json('data.id');

    $teacher2User = makeTeacherUser(2);
    Passport::actingAs($teacher2User, [], 'api');

    $this->getJson("/api/teacher/courses/{$courseId}")->assertStatus(404);
});

test('a non-teacher is blocked from the teacher course routes', function () {
    $admin = AcademicFixtures::user('Admin');
    Passport::actingAs($admin, [], 'api');

    $this->getJson('/api/teacher/courses')->assertStatus(403);
});

// ── Teacher Enrollment Management ───────────────────────────────────────

test('a teacher only sees enrollments for courses they created', function () {
    $teacherA = makeTeacherUser();
    $teacherB = makeTeacherUser();

    Passport::actingAs($teacherA, [], 'api');
    $courseAId = $this->postJson('/api/teacher/courses', teacherCoursePayload())->json('data.id');

    Passport::actingAs($teacherB, [], 'api');
    $courseBId = $this->postJson('/api/teacher/courses', teacherCoursePayload())->json('data.id');

    $studentA = AcademicFixtures::user('Student');
    $studentB = AcademicFixtures::user('Student');
    Enrollment::create(['institute_id' => 1, 'user_id' => $studentA->id, 'course_id' => $courseAId, 'enrollment_type' => 'one_time', 'enrollment_date' => now(), 'amount_paid' => 100, 'status' => 'active']);
    Enrollment::create(['institute_id' => 1, 'user_id' => $studentB->id, 'course_id' => $courseBId, 'enrollment_type' => 'one_time', 'enrollment_date' => now(), 'amount_paid' => 100, 'status' => 'active']);

    Passport::actingAs($teacherA, [], 'api');
    $response = $this->getJson('/api/teacher/enrollments')->assertOk();

    $courseIds = collect($response->json('data.enrollments'))->pluck('course_id');
    expect($courseIds->all())->toBe([$courseAId]);
});

test('the teacher enrollment list can filter by course_id and student_id', function () {
    $teacher = makeTeacherUser();
    Passport::actingAs($teacher, [], 'api');
    $courseId = $this->postJson('/api/teacher/courses', teacherCoursePayload())->json('data.id');

    $student = AcademicFixtures::user('Student');
    Enrollment::create(['institute_id' => 1, 'user_id' => $student->id, 'course_id' => $courseId, 'enrollment_type' => 'one_time', 'enrollment_date' => now(), 'amount_paid' => 100, 'status' => 'active']);

    $this->getJson("/api/teacher/enrollments?course_id={$courseId}&student_id={$student->id}")
        ->assertOk()
        ->assertJsonPath('data.enrollments.0.student_id', $student->id);
});

test('a teacher cannot see another institute\'s enrollments by passing its course_id directly', function () {
    // Institute-2 course + enrollment, created directly (bypassing the teacher
    // API, which would 404 a cross-institute course_id anyway on create).
    $categoryId = courseCategoryId(2);
    $foreignCourse = new Course;
    $foreignCourse->forceFill([
        'institute_id' => 2, 'branch_id' => 2, 'course_category_id' => $categoryId,
        'title' => 'Foreign Course', 'slug' => 'foreign-course-'.uniqid(),
        'regular_price' => 100, 'offer_price' => 100, 'status' => 'published',
    ]);
    $foreignCourse->save();
    Enrollment::create([
        'institute_id' => 2, 'user_id' => AcademicFixtures::user('Student', 2)->id, 'course_id' => $foreignCourse->id,
        'enrollment_type' => 'one_time', 'enrollment_date' => now(), 'amount_paid' => 100, 'status' => 'active',
    ]);

    $teacher = makeTeacherUser(1);
    Passport::actingAs($teacher, [], 'api');

    $this->getJson("/api/teacher/enrollments?course_id={$foreignCourse->id}")
        ->assertOk()
        ->assertJsonCount(0, 'data.enrollments');
});

test('a non-teacher is blocked from the teacher enrollment list via direct API access', function () {
    $admin = AcademicFixtures::user('Admin');
    Passport::actingAs($admin, [], 'api');

    $this->getJson('/api/teacher/enrollments')->assertStatus(403);
});

test('an unauthenticated request to the teacher course/enrollment routes is rejected', function () {
    $this->getJson('/api/teacher/courses')->assertStatus(401);
    $this->getJson('/api/teacher/enrollments')->assertStatus(401);
});
