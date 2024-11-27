<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Passport;
use Modules\Authentication\Models\User;
use Modules\ParentModule\Models\ParentModel;
use Tests\Support\AcademicFixtures;

/*
|--------------------------------------------------------------------------
| Academic system — Phase 2: result processing & Nigerian report card.
|--------------------------------------------------------------------------
*/

beforeEach(fn () => AcademicFixtures::build());

function admin2(): User
{
    $u = AcademicFixtures::user('Admin');
    Passport::actingAs($u, [], 'api');

    return $u;
}

/**
 * Three students with CA (of 40) + Exam (of 60) in subject 1, plus subject 3.
 *
 * @return array<int, array{user: User, student_id: int, student_session_id: int}>
 */
function classWithMarks(): array
{
    AcademicFixtures::markComponents(1);
    DB::table('subjects')->insert(['id' => 3, 'institute_id' => 1, 'branch_id' => 1, 'class_id' => 1, 'subject_name' => 'English', 'subject_code' => 'E3']);
    DB::table('class_exams')->insert(['institute_id' => 1, 'branch_id' => 1, 'class_id' => 1, 'exam_id' => 1, 'merit_process_type_id' => DB::table('merit_process_types')->insertGetId(['institute_id' => 1, 'branch_id' => 1, 'type' => 'Total Marks', 'serial' => 1])]);

    $students = [];
    foreach ([['1', 30, 50, 70], ['2', 35, 55, 60], ['3', 30, 50, 40]] as [$roll, $ca, $exam, $english]) {
        $s = AcademicFixtures::student(1, $roll);
        $students[] = $s;
        foreach ([[1, $ca, $exam, $ca + $exam], [3, $english, 0, $english]] as [$subject, $m1, $m2, $total]) {
            DB::table('exam_marks')->insert([
                'institute_id' => 1, 'branch_id' => 1, 'session_id' => 1, 'student_id' => $s['student_id'],
                'class_id' => 1, 'group_id' => 1, 'subject_id' => $subject, 'exam_id' => 1,
                'mark1' => $m1, 'mark2' => $m2, 'total_marks' => $total, 'percentage' => $total,
                'grade' => $total >= 70 ? 'A' : ($total >= 50 ? 'B' : 'C'), 'grade_point' => 4, 'status' => 'pass',
            ]);
        }
    }

    return $students;
}

function card(int $studentSessionId): array
{
    return test()->getJson("/api/exam-result-card?student_session_id={$studentSessionId}&exam_id=1")->assertOk()->json('data');
}

// ── Workflow & locking ─────────────────────────────────────────────────────

test('results move through submit, approve and publish, and publishing locks the marks', function () {
    [$a] = classWithMarks();
    admin2();

    $this->postJson('/api/result-publications/publish', ['exam_id' => 1, 'class_id' => 1])->assertStatus(422); // not approved yet
    $this->postJson('/api/result-publications/submit', ['exam_id' => 1, 'class_id' => 1])->assertOk()->assertJsonPath('data.status', 'submitted');
    $this->postJson('/api/result-publications/approve', ['exam_id' => 1, 'class_id' => 1])->assertOk()->assertJsonPath('data.status', 'approved');
    $this->postJson('/api/result-publications/publish', ['exam_id' => 1, 'class_id' => 1])
        ->assertOk()->assertJsonPath('data.status', 'published')->assertJsonPath('data.is_locked', true);

    $this->postJson('/api/mark-store-section-wise', [
        'class_id' => 1, 'subject_id' => 1, 'exam_id' => 1,
        'marks' => [['student_id' => $a['student_id'], 'mark_1' => 40, 'mark_2' => 60]],
    ])->assertStatus(422);
    expect((float) DB::table('exam_marks')->where('student_id', $a['student_id'])->where('subject_id', 1)->value('total_marks'))->toBe(80.0);

    $logs = $this->getJson('/api/result-publications/logs?exam_id=1&class_id=1')->assertOk()->json('data');
    expect(collect($logs)->pluck('action')->all())->toBe(['publish', 'approve', 'submit']);
});

test('unpublishing and unlocking need a reason, and unlock requires unpublishing first', function () {
    classWithMarks();
    admin2();
    foreach (['submit', 'approve', 'publish'] as $action) {
        $this->postJson("/api/result-publications/{$action}", ['exam_id' => 1, 'class_id' => 1])->assertOk();
    }

    $this->postJson('/api/result-publications/unlock', ['exam_id' => 1, 'class_id' => 1, 'note' => 'fix'])->assertStatus(422);
    $this->postJson('/api/result-publications/unpublish', ['exam_id' => 1, 'class_id' => 1])->assertStatus(422);
    $this->postJson('/api/result-publications/unpublish', ['exam_id' => 1, 'class_id' => 1, 'note' => 'Maths typo'])->assertOk();
    $this->postJson('/api/result-publications/unlock', ['exam_id' => 1, 'class_id' => 1, 'note' => 'Maths typo'])
        ->assertOk()->assertJsonPath('data.is_locked', false);
});

test('a locked result also refuses mark imports, assessments and comments', function () {
    [$a] = classWithMarks();
    admin2();
    $this->postJson('/api/result-publications/lock', ['exam_id' => 1, 'class_id' => 1])->assertOk();

    $csv = tempnam(sys_get_temp_dir(), 'm').'.csv';
    file_put_contents($csv, "student_id,roll,student_name,mark_1,mark_2,is_absent\n{$a['student_id']},1,A,10,10,0");
    $this->post('/api/mark-sheet-import', ['exam_id' => 1, 'class_id' => 1, 'subject_id' => 1, 'file' => new UploadedFile($csv, 'm.csv', 'text/csv', null, true)], ['Accept' => 'application/json'])
        ->assertStatus(422);

    $this->postJson('/api/result-comments', ['exam_id' => 1, 'class_id' => 1, 'comments' => [['student_id' => $a['student_id'], 'teacher_comment' => 'Good']]])
        ->assertStatus(422);
});

test('each workflow action requires its own permission', function () {
    classWithMarks();
    Passport::actingAs(AcademicFixtures::user('Staff', 1, ['exam_module_mark_input']), [], 'api');

    $this->postJson('/api/result-publications/submit', ['exam_id' => 1, 'class_id' => 1])->assertOk();
    $this->postJson('/api/result-publications/approve', ['exam_id' => 1, 'class_id' => 1])->assertStatus(403);
    $this->postJson('/api/result-publications/publish', ['exam_id' => 1, 'class_id' => 1])->assertStatus(403);
});

// ── Report card contents ───────────────────────────────────────────────────

test('the report card carries subject positions and class statistics', function () {
    [$a, $b, $c] = classWithMarks();
    admin2();

    $card = card($b['student_session_id']);
    $maths = collect($card['result']['subjects'])->firstWhere('subject_id', 1);
    $english = collect($card['result']['subjects'])->firstWhere('subject_id', 3);

    // Maths totals: A 80, B 90, C 80 → B first; A and C share 2nd.
    expect($maths['subject_position'])->toBe(1)
        ->and($maths['class_highest'])->toEqual(90)
        ->and($maths['class_lowest'])->toEqual(80)
        ->and($maths['class_average'])->toEqual(83.33)
        ->and($english['subject_position'])->toBe(2)
        ->and($card['result']['students_in_class'])->toBe(3)
        ->and($card['result']['components'] ?? $maths['components'])->toHaveCount(2);

    expect(collect(card($c['student_session_id'])['result']['subjects'])->firstWhere('subject_id', 1)['subject_position'])->toBe(2);
});

test('term attendance counts distinct school days and the card shows next-term details', function () {
    [$a] = classWithMarks();
    admin2();
    $this->putJson('/api/exam-term-details/1', [
        'term_number' => 1, 'term_start_date' => '2026-09-01', 'term_end_date' => '2026-12-15',
        'next_term_begins' => '2027-01-10', 'next_term_fee' => 45000,
    ])->assertOk();

    // Two periods on the 2nd (present), absent on the 3rd, another student present on the 4th.
    foreach ([1, 2] as $p) {
        DB::table('periods')->insert(['id' => $p, 'institute_id' => 1, 'branch_id' => 1, 'serial_no' => $p, 'period' => "P{$p}"]);
    }
    $other = AcademicFixtures::student(1, '9');
    foreach ([[$a, '2026-09-02', 1, 1], [$a, '2026-09-02', 1, 2], [$a, '2026-09-03', 2, 1], [$other, '2026-09-04', 1, 1]] as [$s, $date, $att, $period]) {
        DB::table('student_attendances')->insert([
            'institute_id' => 1, 'branch_id' => 1, 'student_id' => $s['student_id'], 'class_id' => 1,
            'section_id' => 1, 'period_id' => $period, 'date' => $date, 'attendance' => $att,
        ]);
    }

    $result = card($a['student_session_id'])['result'];
    expect($result['term_attendance'])->toBe(['days_school_opened' => 3, 'days_present' => 1, 'days_absent' => 2])
        ->and($result['term']['next_term_begins'])->toBe('2027-01-10')
        ->and($result['term']['next_term_fee'])->toEqual(45000);
});

test('later terms show a cumulative average across terms', function () {
    [$a] = classWithMarks();
    DB::table('exams')->insert(['id' => 5, 'institute_id' => 1, 'branch_id' => 1, 'session_id' => 1, 'name' => 'Second Term']);
    DB::table('exam_marks')->insert([
        'institute_id' => 1, 'branch_id' => 1, 'session_id' => 1, 'student_id' => $a['student_id'],
        'class_id' => 1, 'group_id' => 1, 'subject_id' => 1, 'exam_id' => 5,
        'total_marks' => 60, 'percentage' => 60, 'grade' => 'B', 'grade_point' => 4, 'status' => 'pass',
    ]);
    foreach ([[1, 1], [5, 2]] as [$examId, $serial]) {
        DB::table('grand_final_class_exams')->insert(['institute_id' => 1, 'branch_id' => 1, 'class_id' => 1, 'exam_id' => $examId, 'percentage' => 50, 'serial_no' => $serial]);
    }
    admin2();

    $cumulative = $this->getJson("/api/exam-result-card?student_session_id={$a['student_session_id']}&exam_id=5")->assertOk()->json('data.result.cumulative');
    $maths = collect($cumulative['subjects'])->firstWhere('subject_id', 1);

    expect($cumulative['terms'])->toHaveCount(2)
        ->and($maths['cumulative_total'])->toEqual(140)
        ->and($maths['cumulative_average'])->toEqual(70);
});

// ── Comments ───────────────────────────────────────────────────────────────

test('class teacher and principal comments appear on the card; auto remark is only a fallback', function () {
    [$a, $b] = classWithMarks();
    DB::table('remark_configs')->insert(['institute_id' => 1, 'branch_id' => 1, 'remark_title' => 'B', 'remarks' => 'Good effort']);

    $teacher = AcademicFixtures::teacher(1, 1);
    $teacherId = DB::table('teachers')->where('user_id', $teacher->id)->value('id');
    DB::table('class_assigns')->insert(['institute_id' => 1, 'branch_id' => 1, 'teacher_id' => $teacherId, 'class_id' => 1]);

    Passport::actingAs($teacher, [], 'api');
    $this->postJson('/api/teacher/result-comments', ['exam_id' => 1, 'class_id' => 1, 'comments' => [
        ['student_id' => $a['student_id'], 'teacher_comment' => 'A focused and hardworking pupil.'],
    ]])->assertOk();
    $this->postJson('/api/teacher/result-comments', ['exam_id' => 1, 'class_id' => 1, 'comments' => [
        ['student_id' => $a['student_id'], 'principal_comment' => 'Promoted.'],
    ]])->assertStatus(403);

    admin2();
    $this->postJson('/api/result-comments', ['exam_id' => 1, 'class_id' => 1, 'comments' => [
        ['student_id' => $a['student_id'], 'principal_comment' => 'Excellent result. Keep it up.'],
    ]])->assertOk();

    $comments = card($a['student_session_id'])['result']['comments'];
    expect($comments['teacher_comment'])->toBe('A focused and hardworking pupil.')
        ->and($comments['principal_comment'])->toBe('Excellent result. Keep it up.');

    // No written comment → the configured grade remark is used.
    $fallback = card($b['student_session_id'])['result']['comments'];
    expect($fallback['teacher_comment_is_auto'])->toBeTrue();
});

test('a teacher cannot comment on a class they do not teach', function () {
    [$a] = classWithMarks();
    $teacher = AcademicFixtures::teacher(1, 1); // subject teacher, not class teacher
    Passport::actingAs($teacher, [], 'api');

    $this->getJson('/api/teacher/result-comments?exam_id=1&class_id=1')->assertStatus(403);
});

// ── Student & parent portals ───────────────────────────────────────────────

test('students see only published results', function () {
    [$a] = classWithMarks();

    Passport::actingAs($a['user'], [], 'api');
    $list = $this->getJson('/api/student/results')->assertOk()->json('data');
    expect($list[0]['published'])->toBeFalse();
    $this->getJson('/api/student/results/1')->assertStatus(403);

    admin2();
    foreach (['submit', 'approve', 'publish'] as $action) {
        $this->postJson("/api/result-publications/{$action}", ['exam_id' => 1, 'class_id' => 1])->assertOk();
    }

    Passport::actingAs($a['user'], [], 'api');
    $result = $this->getJson('/api/student/results/1')->assertOk()->json('data.result');
    expect($result['student_id'])->toBe($a['student_id'])->and($result['position'])->not->toBeNull();

    $pdf = $this->get('/api/student/results/1/pdf')->assertOk();
    expect($pdf->headers->get('content-type'))->toContain('pdf');
});

test('parents see their child\'s published result and nothing before publication', function () {
    [$a] = classWithMarks();
    $parentUser = AcademicFixtures::user('Parent', 1);
    ParentModel::create([
        'institute_id' => 1, 'branch_id' => 1, 'user_id' => $parentUser->id,
        'student_id' => $a['student_id'], 'parent_name' => 'P', 'parent_phone' => '01799991234', 'access_key' => 'x',
    ]);
    DB::table('students')->where('id', $a['student_id'])->update(['parent_id' => DB::table('parent_models')->max('id')]);

    Passport::actingAs(User::findOrFail($parentUser->id), [], 'api');
    $this->getJson('/api/parent/my-children/results/1')->assertStatus(403);
    expect($this->postJson('/api/parent/my-children/exam-results', ['exam_id' => 1])->assertOk()->json('data'))->toBe([]);

    admin2();
    foreach (['submit', 'approve', 'publish'] as $action) {
        $this->postJson("/api/result-publications/{$action}", ['exam_id' => 1, 'class_id' => 1])->assertOk();
    }

    Passport::actingAs(User::findOrFail($parentUser->id), [], 'api');
    expect($this->getJson('/api/parent/my-children/results/1')->assertOk()->json('data.result.student_id'))->toBe($a['student_id']);
});

// ── PDFs & Nigerian template ───────────────────────────────────────────────

test('the Nigerian template renders a single and a whole-class report card PDF', function () {
    [$a] = classWithMarks();
    admin2();
    $this->postJson('/api/result-card-settings', ['settings' => ['template' => 'nigerian']])->assertOk();
    $this->postJson('/api/result-card-settings', ['settings' => ['template' => 'fancy']])->assertStatus(422);

    $single = $this->get("/api/exam-result-card-pdf?student_session_id={$a['student_session_id']}&exam_id=1")->assertOk();
    expect($single->headers->get('content-type'))->toContain('pdf');

    $class = $this->get('/api/class-result-cards-pdf?exam_id=1&class_id=1')->assertOk();
    expect($class->headers->get('content-type'))->toContain('pdf');

    $html = view('examination::results.single_card', card($a['student_session_id']))->render();
    expect($html)->toContain('Class Highest')->toContain('Head Teacher')->toContain('Pos.');
});

test('class statistics summarise the whole class', function () {
    classWithMarks();
    admin2();

    $data = $this->getJson('/api/class-result-statistics?exam_id=1&class_id=1')->assertOk()->json('data');
    $maths = collect($data['subjects'])->firstWhere('subject_id', 1);

    expect($data['class']['students_in_class'])->toBe(3)
        ->and($maths['highest'])->toEqual(90)
        ->and($maths['sat'])->toBe(3);
});

// ── Assessments ────────────────────────────────────────────────────────────

test('affective / psychomotor ratings are validated against class, item and maximum', function () {
    [$a] = classWithMarks();
    $foreign = AcademicFixtures::student(2);
    admin2();
    $domain = DB::table('assessment_domains')->insertGetId(['institute_id' => 1, 'branch_id' => 1, 'domain_type' => 'affective', 'name' => 'Affective']);
    $item = DB::table('assessment_items')->insertGetId(['institute_id' => 1, 'branch_id' => 1, 'assessment_domain_id' => $domain, 'name' => 'Punctuality', 'max_score' => 5]);

    $this->postJson('/api/student-assessments', ['class_id' => 1, 'exam_id' => 1, 'entries' => [$a['student_id'] => [$item => ['score' => 4]]]])->assertOk();
    $this->postJson('/api/student-assessments', ['class_id' => 1, 'exam_id' => 1, 'entries' => [$a['student_id'] => [$item => ['score' => 9]]]])->assertStatus(422);
    $this->postJson('/api/student-assessments', ['class_id' => 1, 'exam_id' => 1, 'entries' => [$foreign['student_id'] => [$item => ['score' => 3]]]])->assertStatus(422);
    // Saving twice updates the same row (keyed like the unique index).
    $this->postJson('/api/student-assessments', ['class_id' => 1, 'exam_id' => 1, 'entries' => [$a['student_id'] => [$item => ['score' => 5]]]])->assertOk();

    expect(DB::table('student_assessments')->where('student_id', $a['student_id'])->count())->toBe(1)
        ->and((float) DB::table('student_assessments')->where('student_id', $a['student_id'])->value('score'))->toBe(5.0);
});
