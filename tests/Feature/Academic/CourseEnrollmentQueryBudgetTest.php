<?php

use Illuminate\Support\Facades\DB;
use Laravel\Passport\Passport;
use Modules\Elearning\Models\Course;
use Modules\Elearning\Models\Enrollment;
use Tests\Support\AcademicFixtures;

/*
|--------------------------------------------------------------------------
| Final audit (Phase 12-16) — performance check for the new Phase 9-11
| Course/Enrollment endpoints: total query count must not scale with the
| number of rows returned (no N+1 from the eager-loaded relations).
|--------------------------------------------------------------------------
*/

beforeEach(fn () => AcademicFixtures::build());

function makeQueryBudgetCourse(int $n): Course
{
    $categoryId = DB::table('course_categories')->insertGetId([
        'institute_id' => 1, 'name' => 'Cat', 'slug' => 'cat-qb-'.$n.'-'.uniqid(),
        'created_at' => now(), 'updated_at' => now(),
    ]);

    $course = new Course;
    $course->forceFill([
        'institute_id' => 1, 'branch_id' => 1, 'course_category_id' => $categoryId,
        'title' => "QB Course {$n}", 'slug' => 'qb-course-'.$n.'-'.uniqid(),
        'regular_price' => 100, 'offer_price' => 100, 'status' => 'published',
    ]);
    $course->save();

    return $course;
}

function countQueries(callable $callback): int
{
    $count = 0;
    DB::listen(function () use (&$count) {
        $count++;
    });
    $callback();

    return $count;
}

test('student available-courses query count does not scale with the number of courses', function () {
    $student = AcademicFixtures::user('Student');
    Passport::actingAs($student, [], 'api');

    makeQueryBudgetCourse(1);
    $queriesForOne = countQueries(fn () => $this->getJson('/api/student/courses')->assertOk());

    for ($i = 2; $i <= 6; $i++) {
        makeQueryBudgetCourse($i);
    }
    $queriesForSix = countQueries(fn () => $this->getJson('/api/student/courses')->assertOk());

    expect($queriesForSix)->toBe($queriesForOne);
});

test('teacher course list query count does not scale with the number of courses', function () {
    $teacherUser = AcademicFixtures::user('Teacher');
    DB::table('teachers')->insert([
        'institute_id' => 1, 'branch_id' => 1, 'user_id' => $teacherUser->id, 'department_id' => null,
        'name' => 'Teacher', 'gender' => 'Male', 'phone' => $teacherUser->phone,
    ]);
    Passport::actingAs($teacherUser, [], 'api');

    $categoryId = DB::table('course_categories')->insertGetId([
        'institute_id' => 1, 'name' => 'Cat', 'slug' => 'cat-tqb-'.uniqid(), 'created_at' => now(), 'updated_at' => now(),
    ]);

    $makeOwned = function () use ($categoryId, $teacherUser) {
        $course = new Course;
        $course->forceFill([
            'institute_id' => 1, 'branch_id' => 1, 'course_category_id' => $categoryId, 'created_by' => $teacherUser->id,
            'title' => 'T '.uniqid(), 'slug' => 't-'.uniqid(), 'regular_price' => 100, 'offer_price' => 100, 'status' => 'published',
        ]);
        $course->save();
    };

    $makeOwned();
    $queriesForOne = countQueries(fn () => $this->getJson('/api/teacher/courses')->assertOk());

    for ($i = 0; $i < 5; $i++) {
        $makeOwned();
    }
    $queriesForSix = countQueries(fn () => $this->getJson('/api/teacher/courses')->assertOk());

    expect($queriesForSix)->toBe($queriesForOne);
});

test('teacher enrollment list query count does not scale with the number of enrollments', function () {
    $teacherUser = AcademicFixtures::user('Teacher');
    DB::table('teachers')->insert([
        'institute_id' => 1, 'branch_id' => 1, 'user_id' => $teacherUser->id, 'department_id' => null,
        'name' => 'Teacher', 'gender' => 'Male', 'phone' => $teacherUser->phone,
    ]);
    $course = makeQueryBudgetCourse(1);
    Course::withoutGlobalScopes()->whereKey($course->id)->update(['created_by' => $teacherUser->id]);
    Passport::actingAs($teacherUser, [], 'api');

    $enroll = function () use ($course) {
        Enrollment::create([
            'institute_id' => 1, 'user_id' => AcademicFixtures::user('Student')->id, 'course_id' => $course->id,
            'enrollment_type' => 'one_time', 'enrollment_date' => now(), 'amount_paid' => 100, 'status' => 'active',
        ]);
    };

    $enroll();
    $queriesForOne = countQueries(fn () => $this->getJson('/api/teacher/enrollments')->assertOk());

    for ($i = 0; $i < 5; $i++) {
        $enroll();
    }
    $queriesForSix = countQueries(fn () => $this->getJson('/api/teacher/enrollments')->assertOk());

    expect($queriesForSix)->toBe($queriesForOne);
});
