<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Passport;
use Modules\Authentication\Models\User;
use Modules\ParentModule\Models\ParentModel;
use Modules\Teacher\Services\LessonPlanService;
use Tests\Support\AcademicFixtures;

/*
|--------------------------------------------------------------------------
| Academic system — Phase 4: lesson planning.
|--------------------------------------------------------------------------
*/

beforeEach(function () {
    AcademicFixtures::build();
    DB::table('sections')->insert(['id' => 5, 'institute_id' => 1, 'branch_id' => 1, 'class_id' => 1, 'section_name' => 'B']);
    DB::table('subjects')->insert(['id' => 9, 'institute_id' => 1, 'branch_id' => 1, 'class_id' => 1, 'subject_name' => 'English', 'subject_code' => 'E9']);
});

afterEach(function () {
    // Remove files the upload tests wrote into public/.
    foreach (DB::table('class_lesson_attachments')->pluck('file') as $file) {
        @unlink(base_path('public/'.LessonPlanService::UPLOAD_DIR.$file));
    }
});

function lessonPayload(array $overrides = []): array
{
    return array_merge([
        'title' => 'Fractions', 'topic' => 'Adding fractions', 'date' => '2026-09-21',
        'term_number' => 1, 'week_number' => 3, 'class_id' => 1, 'section_id' => 1, 'subject_id' => 1,
        'objectives' => 'Pupils add fractions with like denominators.',
        'activities' => 'Group work with fraction strips.',
        'evaluation' => 'Five short questions.',
    ], $overrides);
}

test('a teacher creates a lesson plan with attachments for their own subject', function () {
    Passport::actingAs(AcademicFixtures::teacher(1, 1), [], 'api');

    $lesson = $this->post('/api/classlessons', lessonPayload([
        'status' => 'draft',
        'files' => [UploadedFile::fake()->create('plan.pdf', 50, 'application/pdf')],
    ]), ['Accept' => 'application/json'])->assertOk()->json('data');

    expect($lesson['status'])->toBe('draft')
        ->and($lesson['week_number'])->toBe(3)
        ->and($lesson['objectives'])->toContain('like denominators')
        ->and($lesson['attachments'])->toHaveCount(1)
        ->and($lesson['attachments'][0]['original_name'])->toBe('plan.pdf');
});

test('a teacher cannot create a lesson for a subject or section not assigned to them', function () {
    Passport::actingAs(AcademicFixtures::teacher(1, 1), [], 'api');

    $this->postJson('/api/classlessons', lessonPayload(['subject_id' => 9]))->assertStatus(422);
    $this->postJson('/api/classlessons', lessonPayload(['section_id' => 5]))->assertStatus(422);
    $this->postJson('/api/classlessons', lessonPayload(['class_id' => 2, 'section_id' => 2, 'subject_id' => 2]))->assertStatus(422);

    expect(DB::table('class_lessons')->count())->toBe(0);
});

test('a teacher cannot see or change another teacher\'s lessons', function () {
    Passport::actingAs(AcademicFixtures::teacher(1, 1), [], 'api');
    $id = $this->postJson('/api/classlessons', lessonPayload())->assertOk()->json('data.id');

    Passport::actingAs(AcademicFixtures::teacher(9, 5), [], 'api');
    $this->getJson("/api/classlessons/{$id}")->assertStatus(404);
    $this->putJson("/api/classlessons/{$id}", ['title' => 'Hijacked'])->assertStatus(404);
    $this->deleteJson("/api/classlessons/{$id}")->assertStatus(404);
    expect(collect($this->getJson('/api/classlessons')->assertOk()->json('data.data'))->pluck('id'))->not->toContain($id);
});

test('students see only published lessons of their own class and section', function () {
    Passport::actingAs(AcademicFixtures::teacher(1, 1), [], 'api');
    $published = $this->postJson('/api/classlessons', lessonPayload(['title' => 'Published']))->json('data.id');
    $draft = $this->postJson('/api/classlessons', lessonPayload(['title' => 'Draft', 'status' => 'draft']))->json('data.id');

    Passport::actingAs(AcademicFixtures::teacher(9, 5), [], 'api');
    $this->postJson('/api/classlessons', lessonPayload(['title' => 'Other section', 'section_id' => 5, 'subject_id' => 9]))->assertOk();

    $student = AcademicFixtures::student(1); // class 1, section 1
    Passport::actingAs($student['user'], [], 'api');

    $titles = collect($this->getJson('/api/student/lessons')->assertOk()->json('data.data'))->pluck('title');
    expect($titles->all())->toBe(['Published']);
    $this->getJson("/api/student/lessons/{$draft}")->assertStatus(404);
    $this->getJson("/api/student/lessons/{$published}")->assertOk()->assertJsonPath('data.objectives', 'Pupils add fractions with like denominators.');

    // The legacy endpoint is scoped the same way.
    expect(collect($this->getJson('/api/student/classlessons')->assertOk()->json('data.data'))->pluck('title')->all())->toBe(['Published']);
});

test('publishing a draft makes it visible, unpublishing hides it again', function () {
    Passport::actingAs($teacher = AcademicFixtures::teacher(1, 1), [], 'api');
    $id = $this->postJson('/api/classlessons', lessonPayload(['status' => 'draft']))->json('data.id');
    $student = AcademicFixtures::student(1);

    $this->postJson("/api/classlessons/{$id}/publish")->assertOk()->assertJsonPath('data.status', 'published');
    Passport::actingAs($student['user'], [], 'api');
    $this->getJson("/api/student/lessons/{$id}")->assertOk();

    Passport::actingAs($teacher, [], 'api');
    $this->postJson("/api/classlessons/{$id}/unpublish")->assertOk();
    Passport::actingAs($student['user'], [], 'api');
    $this->getJson("/api/student/lessons/{$id}")->assertStatus(404);
});

test('parents see their child\'s published lessons', function () {
    Passport::actingAs(AcademicFixtures::teacher(1, 1), [], 'api');
    $this->postJson('/api/classlessons', lessonPayload())->assertOk();

    $child = AcademicFixtures::student(1);
    $parent = AcademicFixtures::user('Parent', 1);
    ParentModel::create([
        'institute_id' => 1, 'branch_id' => 1, 'user_id' => $parent->id, 'student_id' => $child['student_id'],
        'parent_name' => 'P', 'parent_phone' => '01788887777', 'access_key' => 'x',
    ]);
    DB::table('students')->where('id', $child['student_id'])->update(['parent_id' => DB::table('parent_models')->max('id')]);

    Passport::actingAs(User::findOrFail($parent->id), [], 'api');
    expect($this->getJson('/api/parent/my-children/lessons')->assertOk()->json('data.data'))->toHaveCount(1);
});

test('staff manage all lessons through lesson-plans with the right permission', function () {
    Passport::actingAs(AcademicFixtures::teacher(1, 1), [], 'api');
    $this->postJson('/api/classlessons', lessonPayload())->assertOk();

    Passport::actingAs(AcademicFixtures::user('Staff', 1, ['lesson_plan_view']), [], 'api');
    expect($this->getJson('/api/lesson-plans?week_number=3')->assertOk()->json('data.data'))->toHaveCount(1);
    $this->postJson('/api/lesson-plans', lessonPayload())->assertStatus(403);

    Passport::actingAs(AcademicFixtures::user('Staff'), [], 'api');
    $this->getJson('/api/lesson-plans')->assertStatus(403);

    Passport::actingAs(AcademicFixtures::user('Admin'), [], 'api');
    $this->postJson('/api/lesson-plans', lessonPayload(['section_id' => 5, 'subject_id' => 9]))->assertOk();
});

test('attachments can be added and removed, and unsafe file types are refused', function () {
    Passport::actingAs(AcademicFixtures::teacher(1, 1), [], 'api');
    $id = $this->postJson('/api/classlessons', lessonPayload())->json('data.id');

    $lesson = $this->post("/api/classlessons/{$id}/attachments", [
        'files' => [UploadedFile::fake()->create('notes.docx', 20, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')],
    ], ['Accept' => 'application/json'])->assertOk()->json('data');
    expect($lesson['attachments'])->toHaveCount(1);

    $this->post("/api/classlessons/{$id}/attachments", [
        'files' => [UploadedFile::fake()->create('shell.php', 1, 'application/x-php')],
    ], ['Accept' => 'application/json'])->assertStatus(422);

    $this->deleteJson("/api/classlesson-attachments/{$lesson['attachments'][0]['id']}")->assertOk();
    expect(DB::table('class_lesson_attachments')->count())->toBe(0);
});
