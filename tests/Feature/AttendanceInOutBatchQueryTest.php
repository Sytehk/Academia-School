<?php

use App\Services\AttendanceInOutService;
use Illuminate\Support\Facades\DB;

/**
 * processBatch()'s per-punch loop called firstOrCreate() -- a SELECT followed
 * by an INSERT if not found -- for every punch, even though the punch types
 * already on record had just been preloaded into memory two lines above. A
 * device re-syncing its whole day's window (the common case) means EVERY
 * punch in the batch is already recorded, so every one of those firstOrCreate
 * calls cost a SELECT that only re-confirmed what the preloaded set already knew.
 */
function makePunch(int $studentId, string $time): array
{
    DB::table('classes')->updateOrInsert(['id' => 1], ['institute_id' => 1, 'branch_id' => 1, 'class_name' => 'One']);
    DB::table('sections')->updateOrInsert(['id' => 1], ['institute_id' => 1, 'branch_id' => 1, 'class_id' => 1, 'section_name' => 'A']);
    DB::table('users')->updateOrInsert(
        ['id' => $studentId],
        [
            'institute_id' => 1, 'branch_id' => 1, 'name' => "Student {$studentId}",
            'phone' => '0190000'.$studentId, 'email' => "student{$studentId}@test.local",
            'password' => bcrypt('x'), 'user_type' => 'Student', 'status' => 1,
        ],
    );
    DB::table('students')->updateOrInsert(
        ['id' => $studentId],
        [
            'institute_id' => 1, 'branch_id' => 1, 'user_id' => $studentId,
            'first_name' => "Student{$studentId}", 'phone' => '0190000'.$studentId,
        ],
    );

    return [
        'institute_id' => 1, 'branch_id' => 1, 'student_id' => $studentId,
        'class_id' => 1, 'section_id' => 1, 'date' => now()->format('Y-m-d'),
        'time' => $time, 'qr_code' => 'QR'.$studentId,
        'phone' => '0190000'.$studentId, 'student_name' => "Student {$studentId}",
        'class_name' => 'One', 'section_name' => 'A', 'roll' => (string) $studentId,
    ];
}

it('does not query the attendance_logs table again for punches already known to exist', function () {
    $service = app(AttendanceInOutService::class);

    // First sync: 10 fresh IN punches.
    $firstBatch = [];
    for ($i = 1; $i <= 10; $i++) {
        $firstBatch[] = makePunch($i, '08:00');
    }
    $service->processBatch($firstBatch);

    expect(DB::table('student_attendance_logs')->count())->toBe(10);

    // Second sync: the device re-sends the exact same window (its normal
    // behaviour) -- every one of these 10 punches is already recorded.
    $queries = 0;
    DB::listen(function ($query) use (&$queries) {
        if (str_contains($query->sql, 'student_attendance_logs') && ! str_contains($query->sql, 'select `student_id`, `attendance_date`, `attendance_time`, `type`')) {
            $queries++;
        }
    });

    $notifications = $service->processBatch($firstBatch);

    // The one allowed query is the batch preload itself (whereIn/whereIn,
    // excluded above by its distinctive select list); nothing beyond that
    // should touch student_attendance_logs for a batch that is 100% duplicates.
    expect($queries)->toBe(0);
    expect($notifications)->toBe([]);
    expect(DB::table('student_attendance_logs')->count())->toBe(10);
});

it('still records genuinely new punches and returns first-of-type notifications', function () {
    $service = app(AttendanceInOutService::class);

    $notifications = $service->processBatch([
        makePunch(101, '08:00'), // IN
        makePunch(102, '08:05'), // IN
        makePunch(101, '13:00'), // OUT (same student, second punch today)
    ]);

    expect(DB::table('student_attendance_logs')->count())->toBe(3);
    expect(collect($notifications)->pluck('event')->all())->toBe(['attendance_in', 'attendance_in', 'attendance_out']);
    expect(collect($notifications)->pluck('student_id')->all())->toBe([101, 102, 101]);
});

it('does not double-insert or double-notify a punch repeated within the same batch', function () {
    $service = app(AttendanceInOutService::class);

    $punch = makePunch(201, '08:00');

    $notifications = $service->processBatch([$punch, $punch]);

    expect(DB::table('student_attendance_logs')->count())->toBe(1);
    expect($notifications)->toHaveCount(1);
});
