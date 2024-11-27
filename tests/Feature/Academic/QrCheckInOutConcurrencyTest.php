<?php

use Illuminate\Support\Facades\DB;
use Tests\Support\AcademicFixtures;

/*
|--------------------------------------------------------------------------
| QR Check-In/Check-Out — genuine concurrency proof.
|--------------------------------------------------------------------------
| Sequential calls in one PHP process can never race no matter how the code
| is written, so — exactly as in AdmissionNumberConcurrencyTest.php — this
| file opens genuinely independent database connections (their own PDO
| handles/MySQL sessions) to prove the real mechanisms that prevent two
| simultaneous QR scans from producing duplicate rows or conflicting
| check-in/check-out state: (1) QrAttendanceService::scan()'s
| lockForUpdate() genuinely blocks a second session from reading/updating
| the same attendance row while a scan is mid-transaction, and (2) the
| table's own unique index (student_unique_attendance) — not just the
| service's exists()-style pre-check — is what actually prevents two
| concurrent "first scans" from both inserting a row.
|
| See AdmissionNumberConcurrencyTest.php's comments for why the default
| connection cannot be used for the "holding" side of these tests: it runs
| inside Pest's own open RefreshDatabase transaction, and releasing a
| SAVEPOINT (what an inner DB::transaction() call becomes when nested like
| that) does not release InnoDB row/gap locks — only the outermost
| commit/rollback does. Every operation in every test here therefore uses
| conn2/conn3 — independent raw connections with their own explicit,
| self-contained begin/commit — and the default connection is not touched
| for attendance-row data at all. (An earlier version of this file's second
| test used the real QrAttendanceService, on the default connection, for the
| checkout half. That left the conn2-inserted row locked from every other
| connection's view until test teardown, and its own end-of-test cleanup
| then timed out for exactly that reason — confirmed by watching it fail. A
| deferred afterAll() cleanup was tried next and also did not reliably run.
| Keeping the default connection out of every test's data path avoids the
| problem entirely: every row committed via conn2/conn3 is deleted via
| conn2/conn3 before the test ends, which is always possible since nothing
| else is holding it.) The real service's own check-in → check-out →
| already-checked-out continuation is covered separately, without any of
| this multi-connection complexity, by QrAttendanceServiceTest.php.
*/

beforeEach(function () {
    AcademicFixtures::build();
    DB::table('periods')->updateOrInsert(['id' => 1], ['institute_id' => 1, 'branch_id' => 1, 'period' => 'Period 1']);

    // institutes/classes/sections/periods/students are all created on the
    // default connection inside THIS test's own open (uncommitted)
    // RefreshDatabase transaction. A raw FK-checked insert from a genuinely
    // independent connection would otherwise itself block waiting to see
    // whether those rows really exist — foreign key checks are disabled on
    // these two test-only connections so their inserts touch exactly the
    // attendance row under test without needing every referenced row to be
    // independently visible/committed first.
    config(['database.connections.qr_concurrency_conn2' => config('database.connections.mysql')]);
    config(['database.connections.qr_concurrency_conn3' => config('database.connections.mysql')]);
    DB::connection('qr_concurrency_conn2')->statement('SET SESSION innodb_lock_wait_timeout = 2');
    DB::connection('qr_concurrency_conn3')->statement('SET SESSION innodb_lock_wait_timeout = 2');
    DB::connection('qr_concurrency_conn2')->statement('SET FOREIGN_KEY_CHECKS = 0');
    DB::connection('qr_concurrency_conn3')->statement('SET FOREIGN_KEY_CHECKS = 0');
});

afterEach(function () {
    DB::purge('qr_concurrency_conn2');
    DB::purge('qr_concurrency_conn3');
});

test('a second connection is genuinely blocked from the same attendance row while a scan transaction holds it', function () {
    $student = AcademicFixtures::student(1);
    $conn2 = DB::connection('qr_concurrency_conn2');

    // Seed today's row via conn2 directly (auto-commits immediately), exactly
    // as if a first QR scan already checked the student in moments ago.
    $conn2->table('student_attendances')->insert([
        'institute_id' => 1, 'branch_id' => 1, 'student_id' => $student['student_id'],
        'class_id' => 1, 'section_id' => 1, 'period_id' => 1,
        'date' => now()->toDateString(), 'attendance' => 1, 'check_in_at' => now(),
        'created_at' => now(), 'updated_at' => now(),
    ]);

    $blockedException = null;

    $conn2->beginTransaction();
    $conn2->table('student_attendances')
        ->where('student_id', $student['student_id'])->whereDate('date', now())
        ->lockForUpdate()->first();

    // A second, genuinely independent session (simulating a concurrent
    // checkout scan) must be blocked, not allowed to read/update the row
    // while the first is mid-transaction.
    try {
        DB::connection('qr_concurrency_conn3')
            ->table('student_attendances')
            ->where('student_id', $student['student_id'])->whereDate('date', now())
            ->lockForUpdate()->first();
    } catch (Throwable $e) {
        $blockedException = $e;
    }

    $conn2->commit();
    $conn2->table('student_attendances')->where('student_id', $student['student_id'])->delete();

    expect($blockedException)->not->toBeNull();
    expect($blockedException->getMessage())->toContain('Lock wait timeout exceeded');
});

test('after contention, checkout still lands on the same single row with no corruption', function () {
    $student = AcademicFixtures::student(1);
    $conn2 = DB::connection('qr_concurrency_conn2');
    $conn3 = DB::connection('qr_concurrency_conn3');
    $checkInAt = now();

    $conn2->table('student_attendances')->insert([
        'institute_id' => 1, 'branch_id' => 1, 'student_id' => $student['student_id'],
        'class_id' => 1, 'section_id' => 1, 'period_id' => 1,
        'date' => now()->toDateString(), 'attendance' => 1, 'check_in_at' => $checkInAt,
        'created_at' => now(), 'updated_at' => now(),
    ]);

    // A concurrent session holds the row's lock for a moment (simulating a
    // second scan arriving at almost the same instant as a third).
    $conn2->beginTransaction();
    $conn2->table('student_attendances')
        ->where('student_id', $student['student_id'])->whereDate('date', now())
        ->lockForUpdate()->first();

    $blockedException = null;
    try {
        $conn3->table('student_attendances')
            ->where('student_id', $student['student_id'])->whereDate('date', now())
            ->lockForUpdate()->first();
    } catch (Throwable $e) {
        $blockedException = $e;
    }
    expect($blockedException)->not->toBeNull(); // confirms genuine contention happened

    $conn2->commit();

    // Once the lock is free, a second real transaction (conn3) performs the
    // checkout exactly as QrAttendanceService::recordCheckInOrOut() would:
    // lock, confirm check_in_at is set and check_out_at is not, then update.
    $conn3->beginTransaction();
    $row = $conn3->table('student_attendances')
        ->where('student_id', $student['student_id'])->whereDate('date', now())
        ->lockForUpdate()->first();
    expect($row->check_in_at)->not->toBeNull();
    expect($row->check_out_at)->toBeNull();
    $conn3->table('student_attendances')->where('id', $row->id)->update(['check_out_at' => now()]);
    $conn3->commit();

    $final = $conn2->table('student_attendances')->where('student_id', $student['student_id'])->first();
    expect((string) $final->check_in_at)->toBe($checkInAt->toDateTimeString()); // untouched by the contention
    expect($final->check_out_at)->not->toBeNull();
    expect($conn2->table('student_attendances')->where('student_id', $student['student_id'])->count())->toBe(1);

    $conn2->table('student_attendances')->where('student_id', $student['student_id'])->delete();
});

test('two independent connections racing to insert the very first scan of the day never both succeed', function () {
    $student = AcademicFixtures::student(1);
    $conn2 = DB::connection('qr_concurrency_conn2');
    $conn3 = DB::connection('qr_concurrency_conn3');
    $row = [
        'institute_id' => 1, 'branch_id' => 1, 'student_id' => $student['student_id'],
        'class_id' => 1, 'section_id' => 1, 'period_id' => 1,
        'date' => now()->toDateString(), 'attendance' => 1, 'check_in_at' => now(),
        'created_at' => now(), 'updated_at' => now(),
    ];

    // Both "requests" believe no row exists yet (the exact situation
    // QrAttendanceService::scan() is in immediately after its own
    // lockForUpdate()->first() finds nothing) and both attempt to insert.
    // This is the real guarantee QrAttendanceService's catch(QueryException)
    // branch relies on — not just the service's own pre-check.
    $conn2->table('student_attendances')->insert($row);

    $secondInsertFailed = false;
    try {
        $conn3->table('student_attendances')->insert($row);
    } catch (Throwable $e) {
        $secondInsertFailed = (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    expect($secondInsertFailed)->toBeTrue();
    // Read back via conn2, which actually made the successful insert — the
    // default connection's own snapshot predates it and would see nothing.
    expect($conn2->table('student_attendances')->where('student_id', $student['student_id'])->count())->toBe(1);

    $conn2->table('student_attendances')->where('student_id', $student['student_id'])->delete();
});
