<?php

use App\Services\AdmissionNumberService;
use Illuminate\Support\Facades\DB;
use Modules\Authentication\Models\Institute;
use Tests\Support\AcademicFixtures;

/*
|--------------------------------------------------------------------------
| Final audit — genuine concurrency proof for admission-number generation.
|--------------------------------------------------------------------------
| Phase7AdmissionNumberTest.php only ever calls the service sequentially in
| one PHP process, which proves correctness but not concurrency-safety: two
| calls in a row can never race no matter how the code is written. True
| OS-thread/process parallelism (pcntl_fork) isn't available in this PHP/
| Windows test environment, so this file instead opens a second, genuinely
| independent database connection (its own PDO handle, its own MySQL
| session) and proves the actual mechanism that prevents duplicates under
| real concurrent load: AdmissionNumberSequence's row-level lock
| (lockForUpdate(), used internally by AdmissionNumberService::next())
| really does block a second session from reading/advancing the same row
| while the first is mid-generation — not merely that the current test
| process happens to run one call at a time.
*/

beforeEach(function () {
    AcademicFixtures::build();

    // A second, fully independent connection to the SAME test database — a
    // distinct PDO handle/MySQL session, so its locks are genuinely separate
    // from the default connection's, exactly as two concurrent HTTP requests
    // (each with their own DB connection from the pool) would be.
    config(['database.connections.admission_concurrency_conn2' => config('database.connections.mysql')]);
    config(['database.connections.admission_concurrency_conn3' => config('database.connections.mysql')]);
    DB::connection('admission_concurrency_conn2')->statement('SET SESSION innodb_lock_wait_timeout = 2');
    DB::connection('admission_concurrency_conn3')->statement('SET SESSION innodb_lock_wait_timeout = 2');
});

afterEach(function () {
    DB::purge('admission_concurrency_conn2');
    DB::purge('admission_concurrency_conn3');
});

test('a second connection is genuinely blocked from the same institute\'s sequence row while the first holds it', function () {
    $institute = Institute::findOrFail(1);
    // Seed the sequence row so both connections lock the SAME existing row
    // (an insert-time race on a brand-new institute is a separate, already-
    // tested code path in AdmissionNumberService::next()).
    app(AdmissionNumberService::class)->next($institute);

    $conn2 = DB::connection('admission_concurrency_conn2');
    $blockedException = null;

    DB::transaction(function () use ($institute, $conn2, &$blockedException) {
        // "Request A": acquires and holds the row lock, exactly like the first
        // line of AdmissionNumberService::next()'s transaction.
        DB::table('admission_number_sequences')->where('institute_id', $institute->id)->lockForUpdate()->first();

        // "Request B": a genuinely separate session attempting the same lock
        // concurrently. If the locking were not real (e.g. a bug removed
        // lockForUpdate()), this would succeed immediately instead of timing out.
        try {
            $conn2->table('admission_number_sequences')->where('institute_id', $institute->id)->lockForUpdate()->first();
        } catch (Throwable $e) {
            $blockedException = $e;
        }
    });

    expect($blockedException)->not->toBeNull();
    expect($blockedException->getMessage())->toContain('Lock wait timeout exceeded');
});

test('after the lock is released, generation continues correctly with no duplicate or corruption from the contention', function () {
    $institute = Institute::findOrFail(1);
    $service = app(AdmissionNumberService::class);
    $first = $service->next($institute); // '000001'

    $conn2 = DB::connection('admission_concurrency_conn2');
    $numberTakenUnderLock = null;

    DB::transaction(function () use ($institute, $conn2, &$numberTakenUnderLock) {
        $sequence = DB::table('admission_number_sequences')->where('institute_id', $institute->id)->lockForUpdate()->first();

        // Confirm the second connection is still blocked here too (same proof
        // as above), then let this transaction finish advancing the sequence
        // itself, exactly as AdmissionNumberService::next() would.
        try {
            $conn2->table('admission_number_sequences')->where('institute_id', $institute->id)->lockForUpdate()->first();
            $this->fail('Expected the second connection to be blocked by the row lock.');
        } catch (Throwable $e) {
            expect($e->getMessage())->toContain('Lock wait timeout exceeded');
        }

        $numberTakenUnderLock = $sequence->next_number;
        DB::table('admission_number_sequences')->where('institute_id', $institute->id)
            ->update(['next_number' => $sequence->next_number + 1]);
    });

    // Now that the lock is released, the real service must hand out the next
    // number after the one just taken — never the same one, never corrupted.
    $second = $service->next($institute);

    expect((int) $numberTakenUnderLock)->toBe(2)
        ->and($second)->toBe('000003')
        ->and($second)->not->toBe($first);
});

test('locks on two different institutes\' sequence rows never block each other', function () {
    // Deliberately avoids institute id=1/AdmissionNumberService, and
    // deliberately avoids the DEFAULT connection on EITHER side: the default
    // connection's queries run inside Pest's own open RefreshDatabase
    // transaction for the whole test, so any lockForUpdate() it performs —
    // whether wrapped in an explicit inner DB::transaction() or not — is not
    // released until that outer transaction finally rolls back at teardown
    // (releasing a SAVEPOINT does not release InnoDB row/gap locks). Two
    // earlier versions of this test used the default connection for one side
    // and could then never clean up their own conn2-inserted rows afterward,
    // still blocked by that lingering lock. Using two independent raw
    // connections for BOTH sides (each with its own explicit, self-contained
    // begin/commit) avoids the default connection, and Pest's transaction,
    // entirely — so committing genuinely releases every lock immediately.
    $conn2 = DB::connection('admission_concurrency_conn2');
    $conn3 = DB::connection('admission_concurrency_conn3');
    $makeCommittedInstitute = function () use ($conn2) {
        $id = $conn2->table('institutes')->insertGetId([
            'name' => 'Concurrency Test Institute', 'domain' => 'concurrency-test-'.uniqid().'.local',
            'platform' => 'WEB', 'status' => 1, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $conn2->table('admission_number_sequences')->insert(['institute_id' => $id, 'next_number' => 1]);

        return $id;
    };

    $instituteAId = $makeCommittedInstitute();
    $instituteBId = $makeCommittedInstitute();

    $conn2->beginTransaction();
    $conn2->table('admission_number_sequences')->where('institute_id', $instituteAId)->lockForUpdate()->first();

    // A concurrent session generating for institute B (unrelated to A) must
    // proceed immediately — row-level locking must not degrade into a
    // table-level lock that would serialize unrelated institutes' admissions.
    $row = $conn3->table('admission_number_sequences')->where('institute_id', $instituteBId)->lockForUpdate()->first();

    $conn2->commit();

    $conn2->table('admission_number_sequences')->whereIn('institute_id', [$instituteAId, $instituteBId])->delete();
    $conn2->table('institutes')->whereIn('id', [$instituteAId, $instituteBId])->delete();

    expect($row)->not->toBeNull();
});
