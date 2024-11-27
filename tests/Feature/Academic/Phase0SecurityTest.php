<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Passport;
use Modules\Authentication\Models\User;
use Modules\ParentModule\Models\ParentModel;
use Tests\Support\AcademicFixtures;

/*
|--------------------------------------------------------------------------
| Academic system — Phase 0: security & architecture.
|--------------------------------------------------------------------------
*/

beforeEach(fn () => AcademicFixtures::build());

function onlineExamFor(int $tenant, array $overrides = []): int
{
    $id = DB::table('online_exams')->insertGetId(array_merge([
        'institute_id' => $tenant, 'branch_id' => $tenant, 'session_id' => 1,
        'exam_id' => $tenant, 'class_id' => $tenant, 'subject_id' => $tenant,
        'name' => 'CBT', 'status' => 'published', 'duration_minutes' => 30, 'attempt_limit' => 1,
        'transfer_policy' => 'ask', 'is_active' => true,
        'created_at' => now(), 'updated_at' => now(),
    ], $overrides));

    DB::table('online_exam_questions')->insert([
        'institute_id' => $tenant, 'branch_id' => $tenant, 'online_exam_id' => $id,
        'type' => 'multiple_choice', 'question_text' => '2 + 2 = ?',
        'options' => json_encode(['3', '4']), 'correct_answer' => json_encode(['4']),
        'marks' => 1, 'display_order' => 1, 'is_active' => true,
    ]);

    return $id;
}

// ── Permission gates ───────────────────────────────────────────────────────

test('a back-office user without exam permissions is refused the examination API', function () {
    Passport::actingAs(AcademicFixtures::user('Staff'), [], 'api');

    $this->getJson('/api/online-exams')->assertStatus(403);
    $this->postJson('/api/mark-store-section-wise', [])->assertStatus(403);
    $this->getJson('/api/tabulation-sheet')->assertStatus(403);
    $this->postJson('/api/exam-grade-store', [])->assertStatus(403);
});

test('a back-office user without question bank permission is refused the question bank API', function () {
    Passport::actingAs(AcademicFixtures::user('Staff'), [], 'api');

    $this->getJson('/api/questions')->assertStatus(403);
    $this->postJson('/api/question-bank-classes', ['name' => 'x'])->assertStatus(403);
});

test('a granted permission opens exactly its own routes', function () {
    Passport::actingAs(AcademicFixtures::user('Staff', 1, ['exam_module_mark_input']), [], 'api');

    // Reaches the controller (validation error, not a 403).
    $this->postJson('/api/mark-store-section-wise', [])->assertStatus(422);
    // Not granted: online exam management.
    $this->getJson('/api/online-exams')->assertStatus(403);
});

test('an institute admin keeps full access to the examination API', function () {
    Passport::actingAs(AcademicFixtures::user('Admin'), [], 'api');

    $this->getJson('/api/online-exams')->assertOk();
    $this->getJson('/api/question-bank-classes')->assertOk();
});

// ── Student class/section authorization ────────────────────────────────────

test('a student cannot start an online exam set for another class through the API', function () {
    $student = AcademicFixtures::student(1);
    DB::table('classes')->insert(['id' => 3, 'institute_id' => 1, 'branch_id' => 1, 'class_name' => 'Class 3']);
    DB::table('subjects')->insert(['id' => 3, 'institute_id' => 1, 'branch_id' => 1, 'class_id' => 3, 'subject_name' => 'Other', 'subject_code' => 'O3']);
    $foreignExam = onlineExamFor(1, ['class_id' => 3, 'subject_id' => 3]);

    Passport::actingAs($student['user'], [], 'api');

    $this->postJson("/api/student/online-exams/{$foreignExam}/start")->assertStatus(403);
    expect(DB::table('online_exam_attempts')->count())->toBe(0);
});

test('a student cannot start an exam restricted to a different section of their class', function () {
    $student = AcademicFixtures::student(1);
    DB::table('sections')->insert(['id' => 5, 'institute_id' => 1, 'branch_id' => 1, 'class_id' => 1, 'section_name' => 'B']);
    $sectionExam = onlineExamFor(1, ['section_id' => 5]);

    Passport::actingAs($student['user'], [], 'api');

    $this->postJson("/api/student/online-exams/{$sectionExam}/start")->assertStatus(403);
});

test('a student can start an online exam for their own class', function () {
    $student = AcademicFixtures::student(1);
    $exam = onlineExamFor(1);

    Passport::actingAs($student['user'], [], 'api');

    $this->postJson("/api/student/online-exams/{$exam}/start")->assertOk();
    expect(DB::table('online_exam_attempts')->where('student_id', $student['student_id'])->count())->toBe(1);
});

// ── Question bank tenant isolation ─────────────────────────────────────────

test('question bank taxonomy created by one institute is invisible to another', function () {
    Passport::actingAs(AcademicFixtures::user('Admin', 1), [], 'api');
    $id = $this->postJson('/api/question-bank-classes', ['name' => 'Tenant One Class'])->assertOk()->json('data.id');

    expect(DB::table('question_bank_classes')->where('id', $id)->value('institute_id'))->toBe(1);

    Passport::actingAs(AcademicFixtures::user('Admin', 2), [], 'api');
    $names = collect($this->getJson('/api/question-bank-classes')->assertOk()->json('data.data'))->pluck('name');
    expect($names)->not->toContain('Tenant One Class');
    $this->getJson("/api/question-bank-classes/{$id}")->assertStatus(500); // repo "does not exist"
    $this->putJson("/api/question-bank-classes/{$id}", ['name' => 'hijacked']);

    expect(DB::table('question_bank_classes')->where('id', $id)->value('name'))->toBe('Tenant One Class');
});

test('legacy shared taxonomy rows are readable by every institute but not editable', function () {
    $sharedId = DB::table('question_bank_classes')->insertGetId(['name' => 'Shared', 'status' => 1, 'institute_id' => null]);

    Passport::actingAs(AcademicFixtures::user('Admin', 2), [], 'api');
    $names = collect($this->getJson('/api/question-bank-classes')->assertOk()->json('data.data'))->pluck('name');
    expect($names)->toContain('Shared');

    $this->putJson("/api/question-bank-classes/{$sharedId}", ['name' => 'Renamed']);
    $this->deleteJson("/api/question-bank-classes/{$sharedId}");

    $row = DB::table('question_bank_classes')->find($sharedId);
    expect($row->name)->toBe('Shared')->and($row->deleted_at)->toBeNull();
});

test('a question cannot reference another institute taxonomy and never takes a client institute id', function () {
    Passport::actingAs(AcademicFixtures::user('Admin', 2), [], 'api');
    $foreignSubject = DB::table('question_bank_subjects')->insertGetId(['name' => 'Foreign', 'status' => 1, 'institute_id' => 2]);

    Passport::actingAs(AcademicFixtures::user('Admin', 1), [], 'api');
    $this->postJson('/api/questions', [
        'question_bank_subject_id' => $foreignSubject,
        'question' => 'What is 2+2?', 'type' => 'multiple_choice',
        'options' => ['3', '4'], 'correct_answer' => ['4'],
    ])->assertStatus(422)->assertJsonValidationErrors('question_bank_subject_id');

    $ownSubject = DB::table('question_bank_subjects')->insertGetId(['name' => 'Own', 'status' => 1, 'institute_id' => 1]);
    $this->postJson('/api/questions', [
        'question_bank_subject_id' => $ownSubject,
        'question' => 'What is 3+3?', 'type' => 'multiple_choice',
        'options' => ['6', '7'], 'correct_answer' => ['6'],
        'institute_id' => 2,
    ])->assertOk();

    expect(DB::table('questions')->where('institute_id', 2)->count())->toBe(0)
        ->and(DB::table('questions')->where('institute_id', 1)->count())->toBe(1);
});

test('the question list never includes another institute questions', function () {
    foreach ([1, 2] as $t) {
        DB::table('questions')->insert([
            'institute_id' => $t, 'type' => 'multiple_choice', 'question' => "Q{$t}",
            'options' => '["a","b"]', 'correct_answer' => '["a"]', 'status' => 'active',
        ]);
    }

    Passport::actingAs(AcademicFixtures::user('Admin', 1), [], 'api');
    $response = $this->getJson('/api/questions')->assertOk()->json();

    expect(json_encode($response))->toContain('Q1')->not->toContain('"Q2"');
});

// ── Quiz answer-key leak & time limit ─────────────────────────────────────

function quizWithQuestion(array $quiz = []): array
{
    $questionId = DB::table('questions')->insertGetId([
        'institute_id' => 1, 'type' => 'multiple_choice', 'question' => 'Capital?',
        'options' => '["Dhaka","Lagos"]', 'correct_answer' => '["Dhaka"]',
        'explanation' => 'secret', 'status' => 'active',
    ]);
    $quizId = DB::table('quizzes')->insertGetId(array_merge([
        'institute_id' => 1, 'branch_id' => 1, 'title' => 'Quiz', 'status' => 'active',
        'question_ids' => json_encode([$questionId]), 'result_visibility' => 'after_review',
        'has_time_limit' => 1, 'time_limit_value' => 10, 'time_limit_unit' => 'minutes',
    ], $quiz));

    return [$quizId, $questionId];
}

test('quiz details never reveal correct answers before submission', function () {
    [$quizId] = quizWithQuestion();
    $student = AcademicFixtures::student(1);
    Passport::actingAs($student['user'], [], 'api');

    $body = json_encode($this->getJson("/api/quiz-details/{$quizId}")->assertOk()->json());

    expect($body)->not->toContain('correct_answer')->not->toContain('secret');
});

test('a quiz submission after the time limit is refused', function () {
    [$quizId, $questionId] = quizWithQuestion(['on_expiry' => 'prevent_submit']);
    $student = AcademicFixtures::student(1);
    $attemptId = DB::table('quiz_attempts')->insertGetId([
        'institute_id' => 1, 'branch_id' => 1, 'quiz_id' => $quizId, 'user_id' => $student['user']->id,
        'status' => 'started', 'started_at' => now()->subMinutes(30),
    ]);
    Passport::actingAs($student['user'], [], 'api');

    $this->postJson('/api/quiz-submit', [
        'quiz_id' => $quizId, 'attempt_id' => $attemptId,
        'answers' => [['question_id' => $questionId, 'selected_options' => ['Dhaka']]],
    ])->assertStatus(422);

    expect(DB::table('quiz_attempts')->where('id', $attemptId)->value('status'))->toBe('started');
});

// ── Parent results use the main result engine ─────────────────────────────

test('parent exam results come from the result engine, not a hardcoded 100-mark scale', function () {
    AcademicFixtures::markComponents(1);
    $child = AcademicFixtures::student(1);
    DB::table('exam_marks')->insert([
        'institute_id' => 1, 'branch_id' => 1, 'session_id' => 1, 'student_id' => $child['student_id'],
        'class_id' => 1, 'group_id' => 1, 'subject_id' => 1, 'exam_id' => 1,
        'mark1' => 30, 'mark2' => 45, 'total_marks' => 75, 'percentage' => 75,
        'grade' => 'A', 'grade_point' => 5, 'status' => 'pass',
    ]);

    $parentUser = AcademicFixtures::user('Parent', 1);
    ParentModel::create([
        'institute_id' => 1, 'branch_id' => 1, 'user_id' => $parentUser->id,
        'student_id' => $child['student_id'], 'parent_name' => 'P', 'parent_phone' => '01799990000', 'access_key' => 'x',
    ]);
    DB::table('students')->where('id', $child['student_id'])->update(['parent_id' => DB::table('parent_models')->max('id')]);
    // Families only see published results (Phase 2).
    DB::table('result_publications')->insert([
        'institute_id' => 1, 'branch_id' => 1, 'session_id' => 1, 'exam_id' => 1, 'class_id' => 1,
        'status' => 'published', 'is_locked' => true, 'published_at' => now(),
    ]);

    Passport::actingAs(User::findOrFail($parentUser->id), [], 'api');
    $data = $this->postJson('/api/parent/my-children/exam-results', ['exam_id' => 1])->assertOk()->json('data');

    expect($data['exam']['total_marks'])->toEqual(100) // CA 40 + Exam 60 from config
        ->and($data['exam']['obtained_marks'])->toEqual(75)
        ->and($data['exam']['grade'])->toBe('A')
        ->and($data['position_in_class'])->toBe(1)
        ->and($data['result_card']['result']['subjects'][0]['components'])->toHaveCount(2);
});

test('a parent cannot request results for another institute exam', function () {
    $child = AcademicFixtures::student(1);
    $parentUser = AcademicFixtures::user('Parent', 1);
    ParentModel::create([
        'institute_id' => 1, 'branch_id' => 1, 'user_id' => $parentUser->id,
        'student_id' => $child['student_id'], 'parent_name' => 'P', 'parent_phone' => '01799990001', 'access_key' => 'x',
    ]);
    Passport::actingAs(User::findOrFail($parentUser->id), [], 'api');

    $this->postJson('/api/parent/my-children/exam-results', ['exam_id' => 2])->assertStatus(422);
});

// ── Mark sheet import / export ─────────────────────────────────────────────

test('the mark sheet export lists the class with its configured components', function () {
    AcademicFixtures::markComponents(1);
    AcademicFixtures::student(1, '1');
    AcademicFixtures::student(1, '2');
    Passport::actingAs(AcademicFixtures::user('Admin'), [], 'api');

    $data = $this->getJson('/api/mark-sheet-export?format=json&exam_id=1&class_id=1&subject_id=1')->assertOk()->json('data');

    expect($data['students'])->toHaveCount(2)
        ->and(array_keys($data['students'][0]))->toContain('mark_1', 'mark_2', 'is_absent')
        ->and($data['components'])->toHaveCount(2);
});

function markCsv(array $rows): UploadedFile
{
    $lines = ['student_id,roll,student_name,mark_1,mark_2,is_absent'];
    foreach ($rows as $r) {
        $lines[] = implode(',', $r);
    }
    $path = tempnam(sys_get_temp_dir(), 'marks').'.csv';
    file_put_contents($path, implode("\n", $lines));

    return new UploadedFile($path, 'marks.csv', 'text/csv', null, true);
}

test('an imported mark sheet is saved through the mark entry engine', function () {
    AcademicFixtures::markComponents(1);
    $a = AcademicFixtures::student(1, '1');
    $b = AcademicFixtures::student(1, '2');
    Passport::actingAs(AcademicFixtures::user('Admin'), [], 'api');

    $this->post('/api/mark-sheet-import', [
        'exam_id' => 1, 'class_id' => 1, 'subject_id' => 1,
        'file' => markCsv([[$a['student_id'], 1, 'A', 35, 50, 0], [$b['student_id'], 2, 'B', 10, 20, 0]]),
    ], ['Accept' => 'application/json'])->assertOk()->assertJsonPath('data.saved', 2);

    $mark = DB::table('exam_marks')->where('student_id', $a['student_id'])->first();
    expect((float) $mark->total_marks)->toBe(85.0)
        ->and($mark->source)->toBe('import')
        ->and(DB::table('exam_marks')->where('student_id', $b['student_id'])->value('status'))->toBe('fail');
});

test('a mark sheet row for a student outside the class rejects the whole import', function () {
    AcademicFixtures::markComponents(1);
    $a = AcademicFixtures::student(1, '1');
    $foreign = AcademicFixtures::student(2, '1');
    Passport::actingAs(AcademicFixtures::user('Admin'), [], 'api');

    $this->post('/api/mark-sheet-import', [
        'exam_id' => 1, 'class_id' => 1, 'subject_id' => 1,
        'file' => markCsv([[$a['student_id'], 1, 'A', 35, 50, 0], [$foreign['student_id'], 1, 'X', 10, 10, 0]]),
    ], ['Accept' => 'application/json'])->assertStatus(422);

    expect(DB::table('exam_marks')->count())->toBe(0);
});

test('a mark above the component maximum is rejected', function () {
    AcademicFixtures::markComponents(1);
    $a = AcademicFixtures::student(1, '1');
    Passport::actingAs(AcademicFixtures::user('Admin'), [], 'api');

    $this->post('/api/mark-sheet-import', [
        'exam_id' => 1, 'class_id' => 1, 'subject_id' => 1,
        'file' => markCsv([[$a['student_id'], 1, 'A', 45, 50, 0]]), // CA max is 40
    ], ['Accept' => 'application/json'])->assertStatus(422);

    expect(DB::table('exam_marks')->count())->toBe(0);
});
