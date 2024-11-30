<?php

use App\Services\AcademicContextService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Authentication\Models\User;

/**
 * ExamMarkInputController@markStoreSectionWise called
 * assertStudentsInClassSection($studentIds, $classId, null, $groupId) — four
 * arguments against a method declared (array $studentIds, int $classId,
 * int $sectionId). PHP silently drops the extra 4th argument on a normal (not
 * variadic) method call, so the group was never actually validated: a mark
 * save could be posted against any group in the class, not just the one the
 * students in the payload actually belong to.
 */
it('rejects students who belong to a different group than the one asserted', function () {
    $admin = User::factory()->create([
        'institute_id' => 1, 'branch_id' => 1, 'user_type' => 'Admin', 'status' => 1,
    ]);
    Auth::login($admin);

    DB::table('academic_years')->updateOrInsert(['id' => 1], ['session' => '2026', 'year' => '2026']);
    DB::table('settings')->updateOrInsert(
        ['institute_id' => 1, 'branch_id' => 1, 'name' => 'academic_year'],
        ['value' => '1'],
    );
    clear_settings_map();

    DB::table('classes')->updateOrInsert(['id' => 1], ['institute_id' => 1, 'branch_id' => 1, 'class_name' => 'One']);
    DB::table('sections')->updateOrInsert(['id' => 1], ['institute_id' => 1, 'branch_id' => 1, 'class_id' => 1, 'section_name' => 'A']);
    DB::table('student_groups')->updateOrInsert(['id' => 10], ['institute_id' => 1, 'branch_id' => 1, 'group_name' => 'Science']);
    DB::table('student_groups')->updateOrInsert(['id' => 20], ['institute_id' => 1, 'branch_id' => 1, 'group_name' => 'Arts']);

    DB::table('users')->updateOrInsert(
        ['id' => 601],
        [
            'institute_id' => 1, 'branch_id' => 1, 'name' => 'Group Student',
            'phone' => '01955550001', 'email' => 'groupstudent@test.local',
            'password' => bcrypt('12345678'), 'user_type' => 'Student', 'status' => 1,
        ],
    );
    DB::table('students')->updateOrInsert(
        ['id' => 601],
        ['institute_id' => 1, 'branch_id' => 1, 'user_id' => 601, 'first_name' => 'Group', 'phone' => '01955550001'],
    );
    // The student belongs to group 20 (Arts) only.
    DB::table('student_sessions')->updateOrInsert(
        ['id' => 601],
        [
            'institute_id' => 1, 'branch_id' => 1, 'session_id' => 1, 'student_id' => 601,
            'class_id' => 1, 'section_id' => 1, 'group_id' => 20, 'roll' => '1',
        ],
    );

    $service = app(AcademicContextService::class);

    // Asserted against group 10 (Science) -- the student is not a member of it.
    expect(fn () => $service->assertStudentsInClassSection([601], 1, null, 10))
        ->toThrow(ValidationException::class);
});

it('accepts students who do belong to the asserted group', function () {
    $admin = User::factory()->create([
        'institute_id' => 1, 'branch_id' => 1, 'user_type' => 'Admin', 'status' => 1,
    ]);
    Auth::login($admin);

    DB::table('academic_years')->updateOrInsert(['id' => 1], ['session' => '2026', 'year' => '2026']);
    DB::table('settings')->updateOrInsert(
        ['institute_id' => 1, 'branch_id' => 1, 'name' => 'academic_year'],
        ['value' => '1'],
    );
    clear_settings_map();

    DB::table('classes')->updateOrInsert(['id' => 1], ['institute_id' => 1, 'branch_id' => 1, 'class_name' => 'One']);
    DB::table('sections')->updateOrInsert(['id' => 1], ['institute_id' => 1, 'branch_id' => 1, 'class_id' => 1, 'section_name' => 'A']);
    DB::table('student_groups')->updateOrInsert(['id' => 20], ['institute_id' => 1, 'branch_id' => 1, 'group_name' => 'Arts']);

    DB::table('users')->updateOrInsert(
        ['id' => 602],
        [
            'institute_id' => 1, 'branch_id' => 1, 'name' => 'Group Student 2',
            'phone' => '01955550002', 'email' => 'groupstudent2@test.local',
            'password' => bcrypt('12345678'), 'user_type' => 'Student', 'status' => 1,
        ],
    );
    DB::table('students')->updateOrInsert(
        ['id' => 602],
        ['institute_id' => 1, 'branch_id' => 1, 'user_id' => 602, 'first_name' => 'Group', 'phone' => '01955550002'],
    );
    DB::table('student_sessions')->updateOrInsert(
        ['id' => 602],
        [
            'institute_id' => 1, 'branch_id' => 1, 'session_id' => 1, 'student_id' => 602,
            'class_id' => 1, 'section_id' => 1, 'group_id' => 20, 'roll' => '2',
        ],
    );

    $service = app(AcademicContextService::class);

    expect(fn () => $service->assertStudentsInClassSection([602], 1, null, 20))
        ->not->toThrow(ValidationException::class);
});
