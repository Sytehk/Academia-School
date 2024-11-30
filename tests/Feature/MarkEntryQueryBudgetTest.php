<?php

use Illuminate\Support\Facades\DB;
use Modules\Examination\Services\MarkEntryService;

/**
 * Guards the fix for the heaviest write path in the product.
 *
 * markStoreSectionWise() saves one subject's marks for a whole section, calling
 * saveSectionWiseMarks() once per student. subject_id and exam_id are identical
 * for every iteration, yet each call re-ran resolveMaxMarks() and
 * resolvePassMarks() (two queries each), resolveGrade() and a ->fresh() re-read.
 * A 60-student save cost roughly 600 queries, ~240 of them byte-for-byte repeats.
 *
 * The budget below is deliberately a ceiling, not an exact figure — it should
 * fail loudly if a per-student lookup is ever reintroduced, without breaking on
 * an incidental extra query.
 */
beforeEach(function () {
    DB::table('academic_years')->updateOrInsert(['id' => 1], ['session' => '2026', 'year' => '2026']);
    DB::table('classes')->updateOrInsert(
        ['id' => 1],
        ['institute_id' => 1, 'branch_id' => 1, 'class_name' => 'One'],
    );
    DB::table('student_groups')->updateOrInsert(
        ['id' => 1],
        ['institute_id' => 1, 'branch_id' => 1, 'group_name' => 'General'],
    );
    DB::table('subjects')->updateOrInsert(
        ['id' => 1],
        [
            'institute_id' => 1, 'branch_id' => 1, 'class_id' => 1, 'group_id' => 1,
            'subject_name' => 'Maths', 'subject_code' => 'M01',
        ],
    );
    DB::table('exams')->updateOrInsert(
        ['id' => 1],
        ['institute_id' => 1, 'branch_id' => 1, 'name' => 'Midterm'],
    );

    // A grade scale, so resolveGrade() has rows to band against.
    foreach ([['A', 80, 100, 5], ['B', 60, 79, 4], ['F', 0, 59, 0]] as $i => [$name, $low, $high, $point]) {
        DB::table('grades')->updateOrInsert(
            ['id' => $i + 1],
            [
                'institute_id' => 1, 'branch_id' => 1, 'grade_name' => $name,
                'grade_number' => $point, 'grade_range' => "{$low}-{$high}",
                'priority' => (string) ($i + 1),
                'number_low' => $low, 'number_high' => $high,
                'point_low' => $point, 'point_high' => $point, 'grade_point' => $point,
            ],
        );
    }

    // 60 students, as in a full section.
    for ($i = 1; $i <= 60; $i++) {
        $id = 1000 + $i;
        DB::table('users')->updateOrInsert(
            ['id' => $id],
            [
                'institute_id' => 1, 'branch_id' => 1, 'name' => "Student {$i}",
                'phone' => '0192'.str_pad((string) $i, 7, '0', STR_PAD_LEFT),
                'email' => "s{$i}@budget.local", 'password' => bcrypt('x'),
                'user_type' => 'Student', 'status' => 1,
            ],
        );
        DB::table('students')->updateOrInsert(
            ['id' => $id],
            [
                'institute_id' => 1, 'branch_id' => 1, 'user_id' => $id,
                'first_name' => "Student{$i}",
                'phone' => '0192'.str_pad((string) $i, 7, '0', STR_PAD_LEFT),
            ],
        );
    }
});

function countQueriesWhileSavingMarks(int $studentCount): int
{
    /** @var MarkEntryService $service */
    // One instance for the whole loop, exactly as the controller holds it.
    $service = app(MarkEntryService::class);

    $queries = 0;
    DB::listen(function () use (&$queries) {
        $queries++;
    });

    for ($i = 1; $i <= $studentCount; $i++) {
        $service->saveSectionWiseMarks(
            [
                'institute_id' => 1, 'branch_id' => 1, 'session_id' => 1,
                'student_id' => 1000 + $i, 'class_id' => 1, 'group_id' => 1,
                'subject_id' => 1, 'exam_id' => 1,
            ],
            [1 => 15, 2 => 15, 3 => 15, 4 => 15, 5 => 10, 6 => 10],
        );
    }

    return $queries;
}

it('saves a full section of marks without a per-student config lookup', function () {
    $queries = countQueriesWhileSavingMarks(60);

    // Per student the write path should now be: one existence check plus one
    // insert. Everything else (mark config, pass marks, grade scale, remarks) is
    // resolved once. Before the fix this was ~600.
    expect($queries)->toBeLessThan(200);

    expect(DB::table('exam_marks')->where('exam_id', 1)->count())->toBe(60);
});

it('scales per student, not per student squared', function () {
    $ten = countQueriesWhileSavingMarks(10);
    DB::table('exam_marks')->delete();
    $forty = countQueriesWhileSavingMarks(40);

    // Fixed setup cost plus a small constant per student: quadrupling the class
    // must not quadruple-and-then-some. Guards against a lookup creeping back
    // inside the loop.
    expect($forty)->toBeLessThan($ten * 4);
});

it('resolves identical mark configuration only once per subject and exam', function () {
    $service = app(MarkEntryService::class);

    $queries = 0;
    DB::listen(function () use (&$queries) {
        $queries++;
    });

    for ($i = 0; $i < 25; $i++) {
        $service->resolveMaxMarks(1, 1);
        $service->resolvePassMarks(1, 1);
    }

    // 50 calls, one underlying config read.
    expect($queries)->toBeLessThanOrEqual(2);
});
