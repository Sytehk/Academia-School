<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Passport;
use Tests\Support\AcademicFixtures;

/*
|--------------------------------------------------------------------------
| Academic system — Phase 1: CBT + Question Bank.
|--------------------------------------------------------------------------
*/

beforeEach(fn () => AcademicFixtures::build());

function asAdmin(): void
{
    Passport::actingAs(AcademicFixtures::user('Admin'), [], 'api');
}

/** Create a draft exam through the API and return its id. */
function draftExam(array $overrides = []): int
{
    asAdmin();

    return test()->postJson('/api/online-exams', array_merge([
        'name' => 'Maths CBT', 'exam_id' => 1, 'class_id' => 1, 'subject_id' => 1,
        'duration_minutes' => 30, 'attempt_limit' => 1, 'transfer_policy' => 'update', 'pass_marks' => 2,
    ], $overrides))->assertOk()->json('data.id');
}

function addQuestion(int $examId, array $q = []): int
{
    return test()->postJson("/api/online-exams/{$examId}/questions", array_merge([
        'type' => 'multiple_choice', 'question_text' => 'Capital of Nigeria?',
        'options' => ['Lagos', 'Abuja', 'Kano'], 'correct_answer' => ['Abuja'], 'marks' => 2,
    ], $q))->assertOk()->json('data.id');
}

function publish(int $examId): void
{
    test()->postJson("/api/online-exams/{$examId}/publish")->assertOk();
}

function bankQuestion(int $tenant, array $overrides = []): int
{
    return DB::table('questions')->insertGetId(array_merge([
        'institute_id' => $tenant, 'type' => 'multiple_choice', 'question' => 'Bank Q '.uniqid(),
        'options' => json_encode(['A', 'B', 'C']), 'correct_answer' => json_encode(['B']),
        'marks' => 1, 'status' => 'active',
    ], $overrides));
}

// ── Draft / publish workflow ─────────────────────────────────────────────

test('a new exam is a draft that students cannot see or start', function () {
    $examId = draftExam();
    addQuestion($examId);
    $student = AcademicFixtures::student(1);

    Passport::actingAs($student['user'], [], 'api');
    expect($this->getJson('/api/student/online-exams')->assertOk()->json('data'))->toBe([]);
    $this->postJson("/api/student/online-exams/{$examId}/start")->assertStatus(422);
});

test('publishing is refused until the paper is ready', function () {
    $examId = draftExam();

    $this->postJson("/api/online-exams/{$examId}/publish")
        ->assertStatus(422)
        ->assertJsonPath('errors.problems.0', 'The exam has no active questions.');

    addQuestion($examId);
    publish($examId);

    expect(DB::table('online_exams')->find($examId)->status)->toBe('published');
});

test('a pool that draws more questions than it holds blocks publishing', function () {
    $examId = draftExam();
    $poolId = $this->postJson("/api/online-exams/{$examId}/pools", ['name' => 'Algebra', 'draw_count' => 3])->assertOk()->json('data.id');
    addQuestion($examId, ['pool_id' => $poolId]);

    $this->postJson("/api/online-exams/{$examId}/publish")->assertStatus(422);
});

test('an invalid answer key is rejected when authoring a question', function () {
    $examId = draftExam();

    $this->postJson("/api/online-exams/{$examId}/questions", [
        'type' => 'multiple_choice', 'question_text' => 'x', 'options' => ['a', 'b'],
        'correct_answer' => ['c'], 'marks' => 1,
    ])->assertStatus(422);
});

// ── Question bank → exam ────────────────────────────────────────────────

test('questions are copied from the question bank and duplicates skipped', function () {
    $examId = draftExam();
    $q1 = bankQuestion(1);
    $q2 = bankQuestion(1, ['type' => 'fill_blank', 'options' => null, 'correct_answer' => json_encode(['Abuja', 'FCT'])]);

    $this->postJson("/api/online-exams/{$examId}/questions/from-bank", ['question_ids' => [$q1, $q2]])
        ->assertOk()->assertJsonPath('data.added', 2);
    $this->postJson("/api/online-exams/{$examId}/questions/from-bank", ['question_ids' => [$q1]])
        ->assertOk()->assertJsonPath('data.skipped', 1);

    $copied = DB::table('online_exam_questions')->where('online_exam_id', $examId)->where('question_id', $q2)->first();
    expect($copied->type)->toBe('fill_blank')
        ->and(json_decode($copied->correct_answer, true))->toBe(['Abuja', 'FCT']);

    // A later change to the bank never alters the exam paper.
    DB::table('questions')->where('id', $q1)->update(['correct_answer' => json_encode(['C'])]);
    $snapshot = DB::table('online_exam_questions')->where('online_exam_id', $examId)->where('question_id', $q1)->value('correct_answer');
    expect(json_decode($snapshot, true))->toBe(['B']);
});

test('another institute bank question cannot be added to an exam', function () {
    $examId = draftExam();
    $foreign = bankQuestion(2);

    $this->postJson("/api/online-exams/{$examId}/questions/from-bank", ['question_ids' => [$foreign]])->assertStatus(422);
    expect(DB::table('online_exam_questions')->where('online_exam_id', $examId)->count())->toBe(0);
});

test('bank questions can be pulled by criteria into a pool', function () {
    $examId = draftExam();
    $subject = DB::table('question_bank_subjects')->insertGetId(['name' => 'Maths', 'status' => 1, 'institute_id' => 1]);
    foreach (range(1, 5) as $i) {
        bankQuestion(1, ['question_bank_subject_id' => $subject]);
    }
    bankQuestion(1); // other subject
    $poolId = $this->postJson("/api/online-exams/{$examId}/pools", ['name' => 'Maths', 'draw_count' => 2])->json('data.id');

    $this->postJson("/api/online-exams/{$examId}/questions/from-bank-criteria", [
        'question_bank_subject_id' => $subject, 'limit' => 10, 'pool_id' => $poolId,
    ])->assertOk()->assertJsonPath('data.added', 5)->assertJsonPath('data.available', 5);

    expect(DB::table('online_exam_questions')->where('pool_id', $poolId)->count())->toBe(5);
});

// ── Dealing questions to attempts ───────────────────────────────────────

test('each student is dealt the pool draw plus fixed questions, stored per attempt', function () {
    $examId = draftExam(['random_questions' => true, 'random_options' => true]);
    $poolId = $this->postJson("/api/online-exams/{$examId}/pools", ['name' => 'Pool', 'draw_count' => 2])->json('data.id');
    foreach (range(1, 6) as $i) {
        addQuestion($examId, ['question_text' => "Pool Q{$i}", 'pool_id' => $poolId, 'marks' => 1]);
    }
    addQuestion($examId, ['question_text' => 'Always', 'marks' => 3]);
    publish($examId);

    expect(DB::table('online_exams')->find($examId)->total_questions)->toBe(3)
        ->and((float) DB::table('online_exams')->find($examId)->total_marks)->toBe(5.0);

    $student = AcademicFixtures::student(1);
    Passport::actingAs($student['user'], [], 'api');
    $paper = $this->postJson("/api/student/online-exams/{$examId}/start")->assertOk()->json('data');

    expect($paper['questions'])->toHaveCount(3)
        ->and(collect($paper['questions'])->pluck('question_text'))->toContain('Always')
        ->and($paper['max_score'])->toEqual(5);

    // Reloading the attempt returns exactly the same paper in the same order.
    $again = $this->getJson("/api/student/online-exam-attempts/{$paper['attempt']['id']}")->assertOk()->json('data');
    expect(collect($again['questions'])->pluck('id')->all())->toBe(collect($paper['questions'])->pluck('id')->all())
        ->and(collect($again['questions'])->pluck('options')->all())->toBe(collect($paper['questions'])->pluck('options')->all());
});

test('random N questions per student draws exactly N', function () {
    $examId = draftExam(['questions_per_attempt' => 4]);
    foreach (range(1, 10) as $i) {
        addQuestion($examId, ['question_text' => "Q{$i}", 'marks' => 1]);
    }
    publish($examId);

    $student = AcademicFixtures::student(1);
    Passport::actingAs($student['user'], [], 'api');
    $paper = $this->postJson("/api/student/online-exams/{$examId}/start")->assertOk()->json('data');

    expect($paper['questions'])->toHaveCount(4);
});

test('the student paper never contains answer keys or explanations', function () {
    $examId = draftExam();
    addQuestion($examId, ['explanation' => 'Abuja became capital in 1991']);
    publish($examId);

    $student = AcademicFixtures::student(1);
    Passport::actingAs($student['user'], [], 'api');
    $body = json_encode($this->postJson("/api/student/online-exams/{$examId}/start")->assertOk()->json());

    expect($body)->not->toContain('correct_answer')->not->toContain('1991');
});

// ── Marking ─────────────────────────────────────────────────────────────

test('objective answers are marked automatically with negative marking and partial credit', function () {
    $examId = draftExam(['negative_marking' => true, 'negative_marks_per_wrong' => 0.5, 'pass_marks' => 5]);
    $mcq = addQuestion($examId, ['marks' => 2]);
    $multi = addQuestion($examId, ['type' => 'multiple_select', 'question_text' => 'Primes', 'options' => ['2', '3', '4'], 'correct_answer' => ['2', '3'], 'marks' => 2]);
    $blank = addQuestion($examId, ['type' => 'fill_blank', 'question_text' => 'Capital?', 'options' => [], 'correct_answer' => ['Abuja', 'FCT'], 'marks' => 2]);
    $mtf = addQuestion($examId, ['type' => 'multiple_true_false', 'question_text' => 'Statements', 'options' => ['S1', 'S2', 'S3', 'S4'], 'correct_answer' => ['true', 'false', 'true', 'false'], 'marks' => 4]);
    $wrong = addQuestion($examId, ['question_text' => 'Wrong one', 'marks' => 1]);
    publish($examId);

    $student = AcademicFixtures::student(1);
    Passport::actingAs($student['user'], [], 'api');
    $attemptId = $this->postJson("/api/student/online-exams/{$examId}/start")->json('data.attempt.id');

    $result = $this->postJson("/api/student/online-exam-attempts/{$attemptId}/submit", ['answers' => [
        ['question_id' => $mcq, 'selected_options' => ['Abuja']],          // 2
        ['question_id' => $multi, 'selected_options' => ['3', '2']],       // 2
        ['question_id' => $blank, 'answer' => '  fct '],                   // 2 (any accepted, case/space-insensitive)
        ['question_id' => $mtf, 'selected_options' => ['true', 'false', 'false', 'false']], // 3 of 4 = 3
        ['question_id' => $wrong, 'selected_options' => ['Lagos']],        // 0 - 0.5
    ]])->assertOk()->json('data');

    expect($result['attempt']['score'])->toEqual(8.5)
        ->and($result['attempt']['max_score'])->toEqual(11)
        ->and($result['attempt']['is_passed'])->toBeTrue()
        ->and($result['attempt']['correct_count'])->toBe(3);
});

test('descriptive answers wait for manual grading and are not transferable until graded', function () {
    $examId = draftExam(['pass_marks' => 4]);
    $mcq = addQuestion($examId, ['marks' => 2]);
    $essay = addQuestion($examId, ['type' => 'descriptive', 'question_text' => 'Explain federalism', 'options' => [], 'correct_answer' => [], 'marks' => 5]);
    publish($examId);

    $student = AcademicFixtures::student(1);
    Passport::actingAs($student['user'], [], 'api');
    $attemptId = $this->postJson("/api/student/online-exams/{$examId}/start")->json('data.attempt.id');
    $this->postJson("/api/student/online-exam-attempts/{$attemptId}/submit", ['answers' => [
        ['question_id' => $mcq, 'selected_options' => ['Abuja']],
        ['question_id' => $essay, 'answer' => 'Power shared between centre and states.'],
    ]])->assertOk()->assertJsonPath('data.pending_grading', true);

    expect(DB::table('online_exam_attempts')->find($attemptId)->grading_status)->toBe('pending');

    asAdmin();
    $queue = $this->getJson("/api/online-exams/{$examId}/grading-queue")->assertOk()->json('data');
    expect($queue)->toHaveCount(1);
    expect($this->getJson("/api/online-exams/{$examId}/transfer-preview")->json('data'))->toBe([]);

    $this->postJson("/api/online-exam-attempts/{$attemptId}/grade", ['grades' => [
        ['attempt_question_id' => $queue[0]['attempt_question_id'], 'marks' => 9],
    ]])->assertStatus(422);

    $this->postJson("/api/online-exam-attempts/{$attemptId}/grade", ['grades' => [
        ['attempt_question_id' => $queue[0]['attempt_question_id'], 'marks' => 4, 'feedback' => 'Good'],
    ]])->assertOk();

    $attempt = DB::table('online_exam_attempts')->find($attemptId);
    expect((float) $attempt->score)->toBe(6.0)
        ->and($attempt->grading_status)->toBe('graded')
        ->and((bool) $attempt->is_passed)->toBeTrue();
    expect($this->getJson("/api/online-exams/{$examId}/transfer-preview")->json('data'))->toHaveCount(1);
});

// ── Timer, expiry and late attempts ─────────────────────────────────────

test('the timer is capped at the exam end time and late starts are refused', function () {
    $examId = draftExam(['duration_minutes' => 60, 'start_at' => now()->subMinutes(5)->toDateTimeString(), 'end_at' => now()->addMinutes(10)->toDateTimeString()]);
    addQuestion($examId);
    publish($examId);

    $student = AcademicFixtures::student(1);
    Passport::actingAs($student['user'], [], 'api');
    $attemptId = $this->postJson("/api/student/online-exams/{$examId}/start")->json('data.attempt.id');
    $expires = DB::table('online_exam_attempts')->find($attemptId)->expires_at;
    expect(strtotime($expires))->toBeLessThanOrEqual(now()->addMinutes(10)->timestamp + 1);

    $late = AcademicFixtures::student(1, '2');
    DB::table('online_exams')->where('id', $examId)->update(['end_at' => now()->subMinute()]);
    Passport::actingAs($late['user'], [], 'api');
    $this->postJson("/api/student/online-exams/{$examId}/start")->assertStatus(422);
});

test('an answer posted after expiry is ignored; only answers saved in time count', function () {
    $examId = draftExam();
    $q = addQuestion($examId);
    publish($examId);

    $student = AcademicFixtures::student(1);
    Passport::actingAs($student['user'], [], 'api');
    $attemptId = $this->postJson("/api/student/online-exams/{$examId}/start")->json('data.attempt.id');
    $this->postJson("/api/student/online-exam-attempts/{$attemptId}/save", ['answers' => [['question_id' => $q, 'selected_options' => ['Lagos']]]])->assertOk();

    DB::table('online_exam_attempts')->where('id', $attemptId)->update(['expires_at' => now()->subSecond()]);
    $this->postJson("/api/student/online-exam-attempts/{$attemptId}/submit", ['answers' => [['question_id' => $q, 'selected_options' => ['Abuja']]]])->assertOk();

    $attempt = DB::table('online_exam_attempts')->find($attemptId);
    expect($attempt->status)->toBe('auto_submitted')->and((float) $attempt->score)->toBe(0.0);
});

test('the scheduler auto-submits abandoned attempts', function () {
    $examId = draftExam();
    $q = addQuestion($examId);
    publish($examId);

    $student = AcademicFixtures::student(1);
    Passport::actingAs($student['user'], [], 'api');
    $attemptId = $this->postJson("/api/student/online-exams/{$examId}/start")->json('data.attempt.id');
    $this->postJson("/api/student/online-exam-attempts/{$attemptId}/save", ['answers' => [['question_id' => $q, 'selected_options' => ['Abuja']]]]);
    DB::table('online_exam_attempts')->where('id', $attemptId)->update(['expires_at' => now()->subMinute()]);

    auth()->forgetGuards();
    Artisan::call('online-exams:auto-submit');

    $attempt = DB::table('online_exam_attempts')->find($attemptId);
    expect($attempt->status)->toBe('auto_submitted')->and((float) $attempt->score)->toBe(2.0);
});

test('closing an exam submits everybody still writing it', function () {
    $examId = draftExam();
    addQuestion($examId);
    publish($examId);

    $student = AcademicFixtures::student(1);
    Passport::actingAs($student['user'], [], 'api');
    $attemptId = $this->postJson("/api/student/online-exams/{$examId}/start")->json('data.attempt.id');

    asAdmin();
    $this->postJson("/api/online-exams/{$examId}/close")->assertOk()->assertJsonPath('data.auto_submitted_attempts', 1);
    expect(DB::table('online_exam_attempts')->find($attemptId)->status)->toBe('auto_submitted');
});

// ── Retakes ─────────────────────────────────────────────────────────────

test('the attempt limit is enforced and the best-score policy picks the best attempt', function () {
    $examId = draftExam(['attempt_limit' => 2, 'score_policy' => 'best']);
    $q = addQuestion($examId);
    publish($examId);

    $student = AcademicFixtures::student(1);
    Passport::actingAs($student['user'], [], 'api');

    $first = $this->postJson("/api/student/online-exams/{$examId}/start")->json('data.attempt.id');
    $this->postJson("/api/student/online-exam-attempts/{$first}/submit", ['answers' => [['question_id' => $q, 'selected_options' => ['Abuja']]]]);
    $second = $this->postJson("/api/student/online-exams/{$examId}/start")->json('data.attempt.id');
    $this->postJson("/api/student/online-exam-attempts/{$second}/submit", ['answers' => [['question_id' => $q, 'selected_options' => ['Kano']]]]);
    $this->postJson("/api/student/online-exams/{$examId}/start")->assertStatus(422);

    expect(DB::table('online_exam_attempts')->find($second)->attempt_no)->toBe(2);

    asAdmin();
    $row = collect($this->getJson("/api/online-exams/{$examId}/results")->assertOk()->json('data.students'))->firstWhere('student_id', $student['student_id']);
    expect($row['counted_attempt_id'])->toBe($first)->and($row['score'])->toEqual(2);
});

// ── Editing after attempts ──────────────────────────────────────────────

test('an attempted exam keeps its paper frozen but allows safe edits', function () {
    $examId = draftExam();
    addQuestion($examId);
    publish($examId);

    $student = AcademicFixtures::student(1);
    Passport::actingAs($student['user'], [], 'api');
    $this->postJson("/api/student/online-exams/{$examId}/start")->assertOk();

    asAdmin();
    $this->postJson("/api/online-exams/{$examId}/questions", [
        'type' => 'true_false', 'question_text' => 'x', 'options' => ['True', 'False'], 'correct_answer' => ['True'], 'marks' => 1,
    ])->assertStatus(422);
    $this->putJson("/api/online-exams/{$examId}", ['duration_minutes' => 90])->assertStatus(422);
    $this->putJson("/api/online-exams/{$examId}", ['name' => 'Renamed CBT'])->assertOk();
    $this->deleteJson("/api/online-exams/{$examId}")->assertStatus(422);

    expect(DB::table('online_exams')->find($examId)->name)->toBe('Renamed CBT');
});

// ── Result visibility ───────────────────────────────────────────────────

test('hidden results are not shown to the student, and answers are revealed only after the exam ends', function () {
    $examId = draftExam(['result_visibility' => 'hidden']);
    $q = addQuestion($examId);
    publish($examId);

    $student = AcademicFixtures::student(1);
    Passport::actingAs($student['user'], [], 'api');
    $attemptId = $this->postJson("/api/student/online-exams/{$examId}/start")->json('data.attempt.id');
    $this->postJson("/api/student/online-exam-attempts/{$attemptId}/submit", ['answers' => [['question_id' => $q, 'selected_options' => ['Abuja']]]])
        ->assertOk()->assertJsonPath('data.result_released', false);

    DB::table('online_exams')->where('id', $examId)->update(['result_visibility' => 'immediate']);
    $open = $this->getJson("/api/student/online-exam-attempts/{$attemptId}/result")->assertOk()->json('data');
    expect($open['answers_revealed'])->toBeFalse()->and(json_encode($open))->not->toContain('correct_answer');

    DB::table('online_exams')->where('id', $examId)->update(['status' => 'closed']);
    $closed = $this->getJson("/api/student/online-exam-attempts/{$attemptId}/result")->assertOk()->json('data');
    expect($closed['answers_revealed'])->toBeTrue()->and($closed['questions'][0]['correct_answer'])->toBe(['Abuja']);
});

test('a student cannot read another student attempt', function () {
    $examId = draftExam();
    addQuestion($examId);
    publish($examId);

    $a = AcademicFixtures::student(1, '1');
    $b = AcademicFixtures::student(1, '2');
    Passport::actingAs($a['user'], [], 'api');
    $attemptId = $this->postJson("/api/student/online-exams/{$examId}/start")->json('data.attempt.id');

    Passport::actingAs($b['user'], [], 'api');
    $this->getJson("/api/student/online-exam-attempts/{$attemptId}")->assertStatus(403);
    $this->postJson("/api/student/online-exam-attempts/{$attemptId}/submit", [])->assertStatus(403);
});

// ── Mark transfer ───────────────────────────────────────────────────────

test('the counted online score transfers into the mark slot', function () {
    AcademicFixtures::markComponents(1);
    $examId = draftExam(['conversion_enabled' => true, 'academic_full_marks' => 40, 'mark_component_slot' => 1]);
    $q = addQuestion($examId, ['marks' => 4]);
    publish($examId);

    $student = AcademicFixtures::student(1);
    Passport::actingAs($student['user'], [], 'api');
    $attemptId = $this->postJson("/api/student/online-exams/{$examId}/start")->json('data.attempt.id');
    $this->postJson("/api/student/online-exam-attempts/{$attemptId}/submit", ['answers' => [['question_id' => $q, 'selected_options' => ['Abuja']]]]);

    asAdmin();
    $this->postJson("/api/online-exams/{$examId}/transfer", ['policy' => 'update'])->assertOk();

    $mark = DB::table('exam_marks')->where('student_id', $student['student_id'])->first();
    expect((float) $mark->mark1)->toBe(40.0)->and($mark->source)->toBe('online');
});

// ── Teacher portal ──────────────────────────────────────────────────────

test('a teacher manages online exams only for their assigned subject', function () {
    DB::table('subjects')->insert(['id' => 9, 'institute_id' => 1, 'branch_id' => 1, 'class_id' => 1, 'subject_name' => 'English', 'subject_code' => 'E9']);
    $teacher = AcademicFixtures::teacher(1, 1);
    Passport::actingAs($teacher, [], 'api');

    $own = $this->postJson('/api/teacher/online-exams', [
        'name' => 'Teacher CBT', 'exam_id' => 1, 'class_id' => 1, 'section_id' => 1, 'subject_id' => 1,
        'duration_minutes' => 20, 'attempt_limit' => 1, 'transfer_policy' => 'ask',
    ])->assertOk()->json('data.id');

    $this->postJson('/api/teacher/online-exams', [
        'name' => 'Not mine', 'exam_id' => 1, 'class_id' => 1, 'subject_id' => 9,
        'duration_minutes' => 20, 'attempt_limit' => 1, 'transfer_policy' => 'ask',
    ])->assertStatus(403);

    // An admin-made exam for another subject is invisible and unmanageable.
    $foreignId = draftExam(['subject_id' => 9]);
    Passport::actingAs($teacher, [], 'api');
    $ids = collect($this->getJson('/api/teacher/online-exams')->assertOk()->json('data'))->pluck('id');
    expect($ids)->toContain($own)->not->toContain($foreignId);
    $this->getJson("/api/teacher/online-exams/{$foreignId}")->assertStatus(403);

    // And the teacher is still kept out of the back-office API.
    $this->getJson('/api/online-exams')->assertStatus(403);
});

// ── Question bank import / export ───────────────────────────────────────

function questionCsv(array $rows): UploadedFile
{
    $head = 'type,question,option_1,option_2,option_3,option_4,option_5,option_6,correct_answer,marks,negative_marks,explanation,question_bank_subject_id,question_bank_chapter_id,question_bank_difficulty_level_id,question_category_id,status';
    $path = tempnam(sys_get_temp_dir(), 'q').'.csv';
    file_put_contents($path, $head."\n".implode("\n", $rows));

    return new UploadedFile($path, 'questions.csv', 'text/csv', null, true);
}

test('questions import from a spreadsheet into the institute bank', function () {
    asAdmin();

    $this->post('/api/questions-import', ['file' => questionCsv([
        'multiple_choice,Capital of Ghana?,Accra,Kumasi,,,,,Accra,2,0,,,,,,active',
        'fill_blank,The largest ocean is the ___,,,,,,,Pacific|Pacific Ocean,1,0,,,,,,active',
        'descriptive,Discuss the causes of WW1,,,,,,,,5,0,,,,,,active',
    ])], ['Accept' => 'application/json'])->assertOk()->assertJsonPath('data.imported', 3);

    expect(DB::table('questions')->where('institute_id', 1)->count())->toBe(3);
    $blank = DB::table('questions')->where('type', 'fill_blank')->first();
    expect(json_decode($blank->correct_answer, true))->toBe(['Pacific', 'Pacific Ocean']);
});

test('one bad row rejects the whole question import', function () {
    asAdmin();
    $foreignSubject = DB::table('question_bank_subjects')->insertGetId(['name' => 'X', 'status' => 1, 'institute_id' => 2]);

    $this->post('/api/questions-import', ['file' => questionCsv([
        'multiple_choice,Good row?,Yes,No,,,,,Yes,1,0,,,,,,active',
        "multiple_choice,Foreign subject?,Yes,No,,,,,Yes,1,0,,{$foreignSubject},,,,active",
        'multiple_choice,Bad key?,Yes,No,,,,,Maybe,1,0,,,,,,active',
    ])], ['Accept' => 'application/json'])->assertStatus(422)->assertJsonValidationErrors(['row_3', 'row_4']);

    expect(DB::table('questions')->count())->toBe(0);
});

test('the question export uses the import headings', function () {
    bankQuestion(1);
    bankQuestion(2);
    asAdmin();

    $response = $this->get('/api/questions-export')->assertOk();
    $csv = $response->streamedContent() ?: file_get_contents($response->baseResponse->getFile()->getPathname());
    $lines = array_values(array_filter(explode("\n", trim($csv))));

    expect($lines[0])->toContain('type')->toContain('correct_answer')
        ->and($lines)->toHaveCount(2); // heading + only this institute's question
});
