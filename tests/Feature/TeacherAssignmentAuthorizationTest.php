<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Passport;
use Modules\Academic\Models\Assignment;
use Modules\Academic\Models\Teacher;
use Modules\Authentication\Models\User;

/*
|--------------------------------------------------------------------------
| Phase 2 — Teacher portal: a teacher may only read / edit / delete an
| assignment they own (created, or for a subject+section assigned to them).
| Before the fix edit/show/update/destroy scoped only by institute+branch,
| and update/destroy called Assignment::find($id) with no null check.
|--------------------------------------------------------------------------
*/

function seedAcademicYear(): int
{
    DB::table('academic_years')->updateOrInsert(['id' => 1], ['session' => '2026', 'year' => '2026']);
    DB::table('settings')->updateOrInsert(
        ['institute_id' => 1, 'name' => 'academic_year'],
        ['branch_id' => 1, 'type' => 'general', 'value' => '1'],
    );

    // settings_map() memoises per institute|branch in a process-static that
    // RefreshDatabase does not roll back — flush it so this test's freshly
    // seeded academic_year is what get_option() returns.
    settings_map(true);

    return 1;
}

function makeTeacher(): Teacher
{
    DB::table('departments')->updateOrInsert(
        ['institute_id' => 1, 'branch_id' => 1, 'department_name' => 'General'],
        ['slug' => 'general'],
    );
    $deptId = DB::table('departments')
        ->where(['institute_id' => 1, 'branch_id' => 1, 'department_name' => 'General'])
        ->value('id');

    return Teacher::factory()->create([
        'institute_id' => 1, 'branch_id' => 1, 'department_id' => $deptId,
    ]);
}

/**
 * A class + section + subject, with $teacher assigned to teach that subject in
 * that section. Returns [classId, sectionId, subjectId].
 */
function assignSubjectToTeacher(Teacher $teacher): array
{
    $classId = DB::table('classes')->insertGetId([
        'institute_id' => 1, 'branch_id' => 1, 'class_name' => 'C'.uniqid(), 'status' => 1,
    ]);
    $sectionId = DB::table('sections')->insertGetId([
        'institute_id' => 1, 'branch_id' => 1, 'class_id' => $classId,
        'section_name' => 'A', 'status' => 1,
    ]);
    $subjectId = DB::table('subjects')->insertGetId([
        'institute_id' => 1, 'branch_id' => 1, 'class_id' => $classId,
        'subject_name' => 'Sub'.uniqid(), 'subject_code' => strtoupper(uniqid()),
    ]);
    DB::table('assign_subjects')->insert([
        'institute_id' => 1, 'branch_id' => 1,
        'teacher_id' => $teacher->id, 'subject_id' => $subjectId, 'section_id' => $sectionId,
    ]);

    return [$classId, $sectionId, $subjectId];
}

function makeAssignment(Teacher $teacher, array $cls, ?int $createdBy = null): Assignment
{
    [$classId, $sectionId, $subjectId] = $cls;

    return Assignment::create([
        'institute_id' => 1, 'branch_id' => 1, 'session_id' => 1,
        'created_by' => $createdBy ?? $teacher->user_id,
        'title' => 'HW', 'deadline' => now()->addWeek()->toDateString(),
        'class_id' => $classId, 'section_id' => $sectionId, 'subject_id' => $subjectId,
        'file' => 'x.pdf',
    ]);
}

beforeEach(fn () => seedAcademicYear());

test('a teacher cannot view another teacher\'s assignment', function () {
    $teacherA = makeTeacher();
    $teacherB = makeTeacher();
    $clsB = assignSubjectToTeacher($teacherB);
    $assignmentB = makeAssignment($teacherB, $clsB);

    Passport::actingAs(User::findOrFail($teacherA->user_id), [], 'api');

    $this->getJson("/api/teacher/assignment/{$assignmentB->id}")->assertStatus(404);
    $this->getJson("/api/teacher/edit-assignment/{$assignmentB->id}")->assertStatus(404);
});

test('a teacher cannot update another teacher\'s assignment', function () {
    $teacherA = makeTeacher();
    $teacherB = makeTeacher();
    $clsA = assignSubjectToTeacher($teacherA);
    $clsB = assignSubjectToTeacher($teacherB);
    $assignmentB = makeAssignment($teacherB, $clsB);

    Passport::actingAs(User::findOrFail($teacherA->user_id), [], 'api');

    $this->postJson("/api/teacher/update-assignment/{$assignmentB->id}", [
        'title' => 'hacked', 'deadline' => now()->addWeek()->toDateString(),
        'class_id' => $clsA[0], 'section_id' => $clsA[1], 'subject_id' => $clsA[2],
    ])->assertStatus(404);

    expect($assignmentB->fresh()->title)->toBe('HW');
});

test('a teacher cannot delete another teacher\'s assignment', function () {
    $teacherA = makeTeacher();
    $teacherB = makeTeacher();
    $assignmentB = makeAssignment($teacherB, assignSubjectToTeacher($teacherB));

    Passport::actingAs(User::findOrFail($teacherA->user_id), [], 'api');

    $this->getJson("/api/teacher/destroy-assignment/{$assignmentB->id}")->assertStatus(404);

    expect(Assignment::withoutGlobalScopes()->find($assignmentB->id))->not->toBeNull();
});

test('a teacher can manage their own assignment', function () {
    $teacher = makeTeacher();
    $cls = assignSubjectToTeacher($teacher);
    $assignment = makeAssignment($teacher, $cls);

    Passport::actingAs(User::findOrFail($teacher->user_id), [], 'api');

    $this->getJson("/api/teacher/assignment/{$assignment->id}")->assertOk();

    $this->postJson("/api/teacher/update-assignment/{$assignment->id}", [
        'title' => 'Updated', 'deadline' => now()->addWeek()->toDateString(),
        'class_id' => $cls[0], 'section_id' => $cls[1], 'subject_id' => $cls[2],
    ])->assertOk();

    expect($assignment->fresh()->title)->toBe('Updated');
});

test('a teacher cannot create an assignment for a subject not assigned to them', function () {
    $teacher = makeTeacher();
    $other = makeTeacher();
    $clsOther = assignSubjectToTeacher($other);

    Passport::actingAs(User::findOrFail($teacher->user_id), [], 'api');

    $this->postJson('/api/teacher/store-assignment', [
        'title' => 'HW', 'deadline' => now()->addWeek()->toDateString(),
        'class_id' => $clsOther[0], 'section_id' => $clsOther[1], 'subject_id' => $clsOther[2],
        'file' => UploadedFile::fake()->create('hw.pdf', 10, 'application/pdf'),
    ])->assertStatus(403);
});

test('an executable upload is rejected', function () {
    $teacher = makeTeacher();
    $cls = assignSubjectToTeacher($teacher);

    Passport::actingAs(User::findOrFail($teacher->user_id), [], 'api');

    $this->postJson('/api/teacher/store-assignment', [
        'title' => 'HW', 'deadline' => now()->addWeek()->toDateString(),
        'class_id' => $cls[0], 'section_id' => $cls[1], 'subject_id' => $cls[2],
        'file' => UploadedFile::fake()->create('shell.php', 5, 'application/x-php'),
    ])->assertStatus(422);
});
