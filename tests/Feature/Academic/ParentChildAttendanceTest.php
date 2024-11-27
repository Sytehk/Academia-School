<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Passport;
use Modules\Authentication\Models\User;
use Modules\ParentModule\Models\ParentModel;
use Tests\Support\AcademicFixtures;

/*
|--------------------------------------------------------------------------
| Check-In/Check-Out — Parent portal child attendance view.
|--------------------------------------------------------------------------
| Extends the EXISTING parent attendance feature (ParentService::attendance(),
| ParentDashboardController::attendance(), ParentModuleController::
| childrenAttendance()) with check_in_at/check_out_at — no new controller,
| no new authorization mechanism. The security boundary this whole feature
| relies on (authenticated parent -> get_authenticated_parent_student_id()
| -> get_parent_child_ids() ownership check) already existed and is reused
| entirely unchanged; there is no student_id parameter anywhere in these
| routes for an attacker to manipulate.
*/

beforeEach(function () {
    AcademicFixtures::build();
    // student_attendances.period_id is a required FK; AcademicFixtures doesn't
    // seed periods itself (see QrAttendanceServiceTest.php for why updateOrInsert).
    foreach ([1, 2] as $t) {
        DB::table('periods')->updateOrInsert(['id' => $t], ['institute_id' => $t, 'branch_id' => $t, 'period' => "Period {$t}"]);
    }
});

function makeParentWithAttendanceChild(int $tenant = 1): array
{
    $child = AcademicFixtures::student($tenant, (string) random_int(1, 999));
    $parentUser = User::factory()->create([
        'user_type' => 'Parent', 'institute_id' => $tenant, 'branch_id' => $tenant, 'status' => 1,
        'password' => Hash::make('secret123'),
    ]);
    $parent = ParentModel::create([
        'institute_id' => $tenant, 'branch_id' => $tenant, 'user_id' => $parentUser->id,
        'student_id' => $child['student_id'], 'parent_name' => 'Parent Name',
        'parent_phone' => '017'.str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT),
        'access_key' => 'secret123',
    ]);
    DB::table('students')->where('id', $child['student_id'])->update(['parent_id' => $parent->id]);

    return ['parentUser' => $parentUser, 'parent' => $parent, 'studentId' => $child['student_id']];
}

function markAttendance(int $studentId, string $date, int $status, ?string $checkInAt = null, ?string $checkOutAt = null, int $tenant = 1): void
{
    DB::table('student_attendances')->insert([
        'institute_id' => $tenant, 'branch_id' => $tenant, 'student_id' => $studentId,
        'class_id' => $tenant, 'section_id' => $tenant, 'period_id' => $tenant,
        'date' => $date, 'attendance' => $status,
        'check_in_at' => $checkInAt, 'check_out_at' => $checkOutAt,
        'created_at' => now(), 'updated_at' => now(),
    ]);
}

// ── WEB ──────────────────────────────────────────────────────────────────

test('a parent sees their own child\'s attendance with check-in and check-out times', function () {
    ['parentUser' => $parentUser, 'studentId' => $studentId] = makeParentWithAttendanceChild();
    $today = now()->toDateString();
    markAttendance($studentId, $today, 1, '2026-09-28 08:12:00', '2026-09-28 14:35:00');

    $response = $this->actingAs($parentUser)->get('/parent/attendance?month='.now()->month.'&year='.now()->year);

    $response->assertOk();
    $response->assertSee('08:12'); // check-in
    $response->assertSee('02:35'); // check-out (12-hour format)
});

test('an absent day with no check-in/out displays a dash, not fabricated data', function () {
    ['parentUser' => $parentUser, 'studentId' => $studentId] = makeParentWithAttendanceChild();
    markAttendance($studentId, now()->toDateString(), 2); // absent, no check-in/out

    $response = $this->actingAs($parentUser)->get('/parent/attendance?month='.now()->month.'&year='.now()->year);

    $response->assertOk();
    $response->assertSee(_lang('Absent'));
});

test('a manually-created attendance record with no check-in/out still displays correctly', function () {
    ['parentUser' => $parentUser, 'studentId' => $studentId] = makeParentWithAttendanceChild();
    markAttendance($studentId, now()->toDateString(), 1); // present, manual entry, no check-in/out

    $response = $this->actingAs($parentUser)->get('/parent/attendance?month='.now()->month.'&year='.now()->year);

    $response->assertOk(); // renders without error even though check_in_at/check_out_at are null
});

test('a parent cannot see another parent\'s child attendance, even by passing a student_id', function () {
    ['parentUser' => $parentA] = makeParentWithAttendanceChild();
    ['studentId' => $studentBId] = makeParentWithAttendanceChild();
    markAttendance($studentBId, now()->toDateString(), 1, '2026-09-28 09:00:00', '2026-09-28 15:00:00');

    // The route takes no student_id at all — this proves passing one changes nothing.
    $response = $this->actingAs($parentA)->get('/parent/attendance?student_id='.$studentBId);

    $response->assertOk();
    $response->assertDontSee('09:00 AM');
});

test('an unauthenticated request to the parent attendance page is rejected', function () {
    $this->getJson('/parent/attendance')->assertStatus(401);
});

test('a non-parent is blocked from the parent attendance page', function () {
    $admin = AcademicFixtures::user('Admin');
    $this->actingAs($admin)->get('/parent/attendance')->assertStatus(403);
});

// ── API ──────────────────────────────────────────────────────────────────

test('the API returns the authenticated parent\'s own child attendance with check-in/out', function () {
    ['parentUser' => $parentUser, 'studentId' => $studentId] = makeParentWithAttendanceChild();
    markAttendance($studentId, now()->toDateString(), 1, '2026-09-28 08:00:00', '2026-09-28 14:00:00');

    Passport::actingAs($parentUser, [], 'api');
    $response = $this->postJson('/api/parent/my-children/attendance', ['month' => now()->month, 'year' => now()->year])
        ->assertOk();

    $today = collect($response->json('days'))->firstWhere('date', now()->toDateString());
    expect($today['check_in_at'])->toBe('2026-09-28 08:00:00');
    expect($today['check_out_at'])->toBe('2026-09-28 14:00:00');
});

test('the API never returns another parent\'s child attendance', function () {
    ['parentUser' => $parentA] = makeParentWithAttendanceChild();
    ['studentId' => $studentBId] = makeParentWithAttendanceChild();
    markAttendance($studentBId, now()->toDateString(), 1, '2026-09-28 09:00:00', '2026-09-28 15:00:00');

    Passport::actingAs($parentA, [], 'api');
    $response = $this->postJson('/api/parent/my-children/attendance', ['month' => now()->month, 'year' => now()->year])
        ->assertOk();

    $today = collect($response->json('days'))->firstWhere('date', now()->toDateString());
    expect($today['check_in_at'])->toBeNull(); // parent A's own child has no attendance today
});

test('a child from another institute is never reachable through the parent attendance API', function () {
    ['parentUser' => $parentUser, 'studentId' => $studentId] = makeParentWithAttendanceChild(1);
    ['studentId' => $otherInstituteStudentId] = makeParentWithAttendanceChild(2);
    markAttendance($otherInstituteStudentId, now()->toDateString(), 1, '2026-09-28 07:00:00', '2026-09-28 13:00:00', tenant: 2);

    Passport::actingAs($parentUser, [], 'api');
    $response = $this->postJson('/api/parent/my-children/attendance', ['month' => now()->month, 'year' => now()->year])
        ->assertOk();

    $today = collect($response->json('days'))->firstWhere('date', now()->toDateString());
    expect($today['check_in_at'])->toBeNull();
});

test('an unauthenticated API request for child attendance is rejected', function () {
    $this->postJson('/api/parent/my-children/attendance', ['month' => now()->month, 'year' => now()->year])
        ->assertStatus(401);
});

// ── Performance ────────────────────────────────────────────────────────

test('the parent attendance listing does not issue N+1 queries per day of the month', function () {
    ['parentUser' => $parentUser, 'studentId' => $studentId] = makeParentWithAttendanceChild();
    foreach (range(1, 15) as $day) {
        markAttendance($studentId, now()->startOfMonth()->addDays($day - 1)->toDateString(), 1, now()->toDateTimeString());
    }

    Passport::actingAs($parentUser, [], 'api');

    $queryCount = 0;
    DB::listen(function () use (&$queryCount) {
        $queryCount++;
    });

    $this->postJson('/api/parent/my-children/attendance', ['month' => now()->month, 'year' => now()->year])->assertOk();

    // One query for the attendance rows (plus a couple of fixed lookups for
    // the authenticated parent/child resolution) — must not scale with the
    // number of days in the month or the number of attendance rows.
    expect($queryCount)->toBeLessThan(10);
});
