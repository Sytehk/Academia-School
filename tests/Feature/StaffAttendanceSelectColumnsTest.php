<?php

use Illuminate\Support\Facades\DB;
use Modules\Authentication\Models\User;

/**
 * AttendanceController::staff_attendance() used to select('*', ...) across a
 * LEFT JOIN onto staff_attendances. institute_id/branch_id/created_at/
 * updated_at exist on BOTH tables; for a staff member with no attendance row
 * yet for the given date, the left-joined side is NULL, and a bare '*'
 * collision has the last-processed (NULL) column win -- silently overwriting
 * the real users.institute_id/branch_id with NULL for exactly the staff who
 * have not been marked yet.
 */
it('does not null out institute_id/branch_id for staff with no attendance row yet', function () {
    $admin = User::factory()->create([
        'institute_id' => 1, 'branch_id' => 1, 'user_type' => 'Admin', 'status' => 1,
    ]);
    $this->actingAs($admin);

    // Deliberately no staff_attendances row for this user on this date -- the
    // exact case the LEFT JOIN produces NULLs for.
    $teacher = User::factory()->create([
        'institute_id' => 1, 'branch_id' => 1, 'user_type' => 'Teacher', 'status' => 1,
        'name' => 'Unmarked Teacher',
    ]);

    $response = $this->get(route('staff_attendance.create', ['user_type' => 'Teacher', 'date' => now()->format('Y-m-d')]));

    $response->assertOk();
    $response->assertViewHas('attendance', function ($attendance) use ($teacher) {
        $row = collect($attendance)->firstWhere('user_id', $teacher->id);

        return $row
            && (int) $row->institute_id === 1
            && (int) $row->branch_id === 1
            && $row->name === 'Unmarked Teacher';
    });
});

it('still shows the correct attendance status for a staff member already marked today', function () {
    $admin = User::factory()->create([
        'institute_id' => 1, 'branch_id' => 1, 'user_type' => 'Admin', 'status' => 1,
    ]);
    $this->actingAs($admin);

    $teacher = User::factory()->create([
        'institute_id' => 1, 'branch_id' => 1, 'user_type' => 'Teacher', 'status' => 1,
        'name' => 'Marked Teacher',
    ]);
    $today = now()->format('Y-m-d');
    DB::table('staff_attendances')->insert([
        'institute_id' => 1, 'branch_id' => 1, 'user_id' => $teacher->id,
        'date' => $today, 'type' => 'Teacher', 'attendance' => 1,
        'start_time' => '09:00', 'end_time' => '17:00',
        'created_at' => now(), 'updated_at' => now(),
    ]);

    $response = $this->get(route('staff_attendance.create', ['user_type' => 'Teacher', 'date' => $today]));

    $response->assertOk();
    $response->assertViewHas('attendance', function ($attendance) use ($teacher) {
        $row = collect($attendance)->firstWhere('user_id', $teacher->id);

        return $row
            && (int) $row->attendance === 1
            && $row->start_time === '09:00:00'
            && (int) $row->institute_id === 1;
    });
});
