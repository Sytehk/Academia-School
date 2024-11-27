<?php

use Illuminate\Support\Facades\DB;
use Laravel\Passport\Passport;
use Tests\Support\AcademicFixtures;

/*
|--------------------------------------------------------------------------
| Academic system — Phase 3: result history & student promotion.
|--------------------------------------------------------------------------
*/

beforeEach(function () {
    AcademicFixtures::build();
    // Target class 3 / section 3 in tenant 1; academic session 2 is "next year".
    DB::table('classes')->insert(['id' => 3, 'institute_id' => 1, 'branch_id' => 1, 'class_name' => 'Class 3']);
    DB::table('sections')->insert(['id' => 3, 'institute_id' => 1, 'branch_id' => 1, 'class_id' => 3, 'section_name' => 'Section 3']);
});

/**
 * Three students in class 1 with first-term totals 80, 45, 20 (subject 1 out of 100).
 *
 * @return array<int, array>
 */
function rankedClass(): array
{
    $students = [];
    foreach ([['1', 80], ['2', 45], ['3', 20]] as [$roll, $total]) {
        $s = AcademicFixtures::student(1, $roll);
        DB::table('exam_marks')->insert([
            'institute_id' => 1, 'branch_id' => 1, 'session_id' => 1, 'student_id' => $s['student_id'],
            'class_id' => 1, 'group_id' => 1, 'subject_id' => 1, 'exam_id' => 1,
            'mark1' => $total, 'total_marks' => $total, 'percentage' => $total,
            'grade' => $total >= 70 ? 'A' : ($total >= 40 ? 'C' : 'F'), 'grade_point' => 1,
            'status' => $total >= 40 ? 'pass' : 'fail',
        ]);
        $students[] = $s;
    }

    return $students;
}

function asAdmin3(): void
{
    Passport::actingAs(AcademicFixtures::user('Admin'), [], 'api');
}

function promotionPlan(array $decisions, array $extra = []): array
{
    return array_merge([
        'from_session_id' => 1, 'to_session_id' => 2, 'class_id' => 1,
        'target_class_id' => 3, 'target_section_id' => 3,
        'rules' => ['basis' => 'exam', 'exam_id' => 1, 'min_average' => 40],
        'decisions' => $decisions,
    ], $extra);
}

test('the preview suggests promote or repeat from the promotion rules', function () {
    [$a, $b, $c] = rankedClass();
    asAdmin3();

    $rows = collect($this->postJson('/api/promotions/preview', [
        'from_session_id' => 1, 'class_id' => 1,
        'rules' => ['basis' => 'exam', 'exam_id' => 1, 'min_average' => 40],
    ])->assertOk()->json('data'))->keyBy('student_id');

    expect($rows[$a['student_id']]['suggested_action'])->toBe('promote')
        ->and($rows[$b['student_id']]['suggested_action'])->toBe('promote')
        ->and($rows[$c['student_id']]['suggested_action'])->toBe('repeat')
        ->and($rows[$a['student_id']]['average'])->toEqual(80);
});

test('promotion creates new enrolments and never rewrites the previous session', function () {
    [$a, $b, $c] = rankedClass();
    asAdmin3();

    $batch = $this->postJson('/api/promotions', promotionPlan([
        ['student_id' => $a['student_id'], 'action' => 'promote'],
        ['student_id' => $b['student_id'], 'action' => 'promote'],
        ['student_id' => $c['student_id'], 'action' => 'repeat'],
    ]))->assertOk()->json('data');

    expect($batch['summary'])->toMatchArray(['promote' => 2, 'repeat' => 1, 'graduate' => 0, 'total' => 3, 'overridden' => 0]);

    // Previous-year rows are untouched.
    foreach ([$a, $b, $c] as $s) {
        $old = DB::table('student_sessions')->find($s['student_session_id']);
        expect($old->session_id)->toBe(1)->and($old->class_id)->toBe(1);
    }

    $new = DB::table('student_sessions')->where('session_id', 2)->orderBy('roll')->get();
    expect($new)->toHaveCount(3)
        ->and($new->where('student_id', $a['student_id'])->first()->class_id)->toBe(3)
        ->and($new->where('student_id', $c['student_id'])->first()->class_id)->toBe(1)
        ->and($new->where('class_id', 3)->pluck('roll')->all())->toBe(['1', '2']);
});

test('a student can never be enrolled twice in the same target session', function () {
    [$a] = rankedClass();
    asAdmin3();

    $this->postJson('/api/promotions', promotionPlan([['student_id' => $a['student_id'], 'action' => 'promote']]))->assertOk();
    $this->postJson('/api/promotions', promotionPlan([['student_id' => $a['student_id'], 'action' => 'promote']]))->assertStatus(422);

    expect(DB::table('student_sessions')->where('session_id', 2)->where('student_id', $a['student_id'])->count())->toBe(1);
});

test('students outside the source class, or the same session as target, are refused', function () {
    [$a] = rankedClass();
    $outsider = AcademicFixtures::student(2);
    asAdmin3();

    $this->postJson('/api/promotions', promotionPlan([['student_id' => $outsider['student_id'], 'action' => 'promote']]))->assertStatus(422);
    $this->postJson('/api/promotions', promotionPlan([['student_id' => $a['student_id'], 'action' => 'promote']], ['to_session_id' => 1]))->assertStatus(422);

    expect(DB::table('student_promotion_batches')->count())->toBe(0);
});

test('graduating students are marked graduated without a new enrolment', function () {
    [$a] = rankedClass();
    asAdmin3();

    $this->postJson('/api/promotions', promotionPlan([['student_id' => $a['student_id'], 'action' => 'graduate']], [
        'target_class_id' => null, 'target_section_id' => null, 'graduating' => true, 'to_session_id' => null,
    ]))->assertOk();

    expect(DB::table('students')->find($a['student_id'])->graduated_session_id)->toBe(1)
        ->and(DB::table('student_sessions')->where('student_id', $a['student_id'])->count())->toBe(1);
});

test('a promotion can be reverted until marks exist in the new session', function () {
    [$a, $b] = rankedClass();
    asAdmin3();

    $first = $this->postJson('/api/promotions', promotionPlan([['student_id' => $a['student_id'], 'action' => 'promote']]))->json('data.id');
    $this->postJson("/api/promotions/{$first}/revert")->assertOk()->assertJsonPath('data.status', 'reverted');
    expect(DB::table('student_sessions')->where('session_id', 2)->count())->toBe(0);

    $second = $this->postJson('/api/promotions', promotionPlan([['student_id' => $b['student_id'], 'action' => 'promote']]))->json('data.id');
    DB::table('exams')->insert(['id' => 7, 'institute_id' => 1, 'branch_id' => 1, 'session_id' => 2, 'name' => 'Next year term']);
    DB::table('exam_marks')->insert([
        'institute_id' => 1, 'branch_id' => 1, 'session_id' => 2, 'student_id' => $b['student_id'],
        'class_id' => 3, 'group_id' => 1, 'subject_id' => 1, 'exam_id' => 7, 'total_marks' => 50, 'grade' => 'B',
    ]);
    $this->postJson("/api/promotions/{$second}/revert")->assertStatus(422);
});

test('promotion requires its permission', function () {
    rankedClass();
    Passport::actingAs(AcademicFixtures::user('Staff'), [], 'api');

    $this->postJson('/api/promotions/preview', ['from_session_id' => 1, 'class_id' => 1])->assertStatus(403);
});

test('the academic record spans every session; families see only published results', function () {
    [$a] = rankedClass();
    asAdmin3();
    $this->postJson('/api/promotions', promotionPlan([['student_id' => $a['student_id'], 'action' => 'promote']]))->assertOk();

    $record = $this->getJson("/api/students/{$a['student_id']}/academic-record")->assertOk()->json('data');
    expect($record['sessions'])->toHaveCount(2)
        ->and($record['sessions'][0]['class_id'])->toBe(1)
        ->and($record['sessions'][0]['terms'][0]['average'])->toEqual(80)
        ->and($record['sessions'][1]['class_id'])->toBe(3)
        ->and($record['promotions'][0]['action'])->toBe('promote');

    Passport::actingAs($a['user'], [], 'api');
    expect($this->getJson('/api/student/academic-record')->assertOk()->json('data.sessions.0.terms'))->toBe([]);

    DB::table('result_publications')->insert([
        'institute_id' => 1, 'branch_id' => 1, 'session_id' => 1, 'exam_id' => 1, 'class_id' => 1,
        'status' => 'published', 'is_locked' => true,
    ]);
    expect($this->getJson('/api/student/academic-record')->assertOk()->json('data.sessions.0.terms'))->toHaveCount(1);
});

test('staff can still open a previous session result card after the year rolls over', function () {
    [$a] = rankedClass();
    // The institute moves on to academic session 2.
    DB::table('settings')->where(['institute_id' => 1, 'name' => 'academic_year'])->update(['value' => '2']);
    clear_settings_map();
    asAdmin3();

    $card = $this->getJson("/api/exam-result-card?student_session_id={$a['student_session_id']}&exam_id=1")->assertOk()->json('data.result');

    expect($card['total_marks'])->toEqual(80)->and($card['position'])->toBe(1);
});
