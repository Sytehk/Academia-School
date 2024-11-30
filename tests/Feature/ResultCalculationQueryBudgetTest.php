<?php

use Illuminate\Support\Facades\DB;
use Modules\Academic\Models\StudentSession;
use Modules\Examination\Services\ResultCalculationService;

/**
 * buildStudentTermResult() loops every subject in the class and called
 * MarkEntryService::resolveMaxMarks()/resolvePassMarks() for each -- a fresh
 * MarkConfig query per subject, all answerable from $componentMap, which is
 * already loaded once for the whole class two lines above the loop.
 */
it('resolves full/pass marks from the already-loaded component map, not a query per subject', function () {
    DB::table('academic_years')->updateOrInsert(['id' => 1], ['session' => '2026', 'year' => '2026']);
    DB::table('classes')->updateOrInsert(['id' => 1], ['institute_id' => 1, 'branch_id' => 1, 'class_name' => 'One']);
    DB::table('sections')->updateOrInsert(['id' => 1], ['institute_id' => 1, 'branch_id' => 1, 'class_id' => 1, 'section_name' => 'A']);
    DB::table('exams')->updateOrInsert(['id' => 1], ['institute_id' => 1, 'branch_id' => 1, 'name' => 'Midterm']);

    $subjectCount = 10;
    for ($i = 1; $i <= $subjectCount; $i++) {
        DB::table('subjects')->updateOrInsert(
            ['id' => $i],
            ['institute_id' => 1, 'branch_id' => 1, 'class_id' => 1, 'subject_name' => "Subject {$i}", 'subject_code' => "S{$i}"],
        );
    }

    DB::table('users')->updateOrInsert(
        ['id' => 9001],
        [
            'institute_id' => 1, 'branch_id' => 1, 'name' => 'Result Student',
            'phone' => '01988880001', 'email' => 'resultstudent@test.local',
            'password' => bcrypt('x'), 'user_type' => 'Student', 'status' => 1,
        ],
    );
    DB::table('students')->updateOrInsert(
        ['id' => 9001],
        ['institute_id' => 1, 'branch_id' => 1, 'user_id' => 9001, 'first_name' => 'Result', 'phone' => '01988880001'],
    );
    $session = StudentSession::create([
        'institute_id' => 1, 'branch_id' => 1, 'session_id' => 1, 'student_id' => 9001,
        'class_id' => 1, 'section_id' => 1, 'roll' => '1',
    ]);

    $service = app(ResultCalculationService::class);

    $queries = 0;
    DB::listen(function () use (&$queries) {
        $queries++;
    });

    $service->buildStudentTermResult($session, 1);

    // Fixed overhead (subjects, marks, componentMap, grade scale, attendance,
    // remarks -- each queried once regardless of subject count) plus a small
    // per-subject residual, not the ~2.4 queries/subject the unfixed code
    // cost on top of that (measured: 24 queries for 10 subjects before this
    // fix, 14 after).
    expect($queries)->toBeLessThan(18);
});
