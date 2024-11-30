<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Authentication\Models\User;

/**
 * staff_attendance_save()'s absent-notification loop called
 * $this->userService->findUserById() once per absent staff member -- one query
 * per absent user, where every id was already sitting in the upsert payload
 * built two lines above.
 */
/**
 * Runs staff_attendance_save() for $absentCount absent teachers and returns
 * how many "single user by id" lookups hit the users table -- the exact
 * pattern findUserById() issues. The request's other work (one SmsLog row and
 * one synchronously-dispatched SendSmsJob per recipient, each several of its
 * own queries) is real, legitimately-scaling per-recipient cost unrelated to
 * this fix, so a raw total-query count would not isolate it. Matching the
 * single-row `where id = ?` shape does.
 */
function countSingleUserLookups(int $absentCount): int
{
    $teacherIds = [];
    for ($i = 1; $i <= $absentCount; $i++) {
        $id = 8000 + $i;
        DB::table('users')->updateOrInsert(
            ['id' => $id],
            [
                'institute_id' => 1, 'branch_id' => 1, 'name' => "Staff {$i}",
                'phone' => '0197'.str_pad((string) $i, 7, '0', STR_PAD_LEFT),
                'email' => "staff{$i}@test.local", 'password' => Hash::make('12345678'),
                'user_type' => 'Teacher', 'status' => 1,
            ],
        );
        $teacherIds[] = $id;
    }

    $singleLookups = 0;
    DB::listen(function ($query) use (&$singleLookups) {
        if (preg_match('/from `?users`?\s+where\s+(`?users`?\.)?`?id`?\s*=\s*\?/i', $query->sql)) {
            $singleLookups++;
        }
    });

    test()->post(route('staff_attendance.save'), [
        'user_id' => $teacherIds,
        // every user marked absent (2), so every one lands in the notification loop
        'attendance' => array_fill(0, count($teacherIds), 2),
        'date' => now()->format('Y-m-d'),
        'type' => 'Teacher',
        'notify_channel' => 'sms',
    ]);

    return $singleLookups;
}

it('looks up absent staff with one whereIn(), not one query per absent user', function () {
    $admin = User::factory()->create([
        'institute_id' => 1, 'branch_id' => 1, 'user_type' => 'Admin', 'status' => 1,
    ]);
    $this->actingAs($admin);

    // A per-user findUserById() loop would issue one such query per absent
    // teacher (15 here). Batched into one whereIn(), it issues zero.
    expect(countSingleUserLookups(15))->toBe(0);

    expect(DB::table('staff_attendances')->where('attendance', 2)->count())->toBe(15);
});
