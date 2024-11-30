<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Authentication\Models\User;

/**
 * teacher-list.blade.php used to render @foreach ($teachers->unique('name') as
 * $data) — a second teacher sharing a display name with another was silently
 * dropped from the list, with no count, no warning, and no way for an admin to
 * notice. The join in TeacherController::index() (teachers -> users -> LEFT JOIN
 * departments) is one-to-one on both sides and can never produce a duplicate
 * row, so there was no real dedup need behind it.
 */
it('shows every teacher even when two share a display name', function () {
    $login = User::factory()->create([
        'institute_id' => 1, 'branch_id' => 1, 'user_type' => 'Admin', 'status' => 1,
    ]);
    $this->actingAs($login);

    foreach ([201, 202] as $userId) {
        DB::table('users')->updateOrInsert(
            ['id' => $userId],
            [
                'institute_id' => 1, 'branch_id' => 1, 'name' => 'Mohammad Ali',
                'phone' => '01700000'.$userId, 'email' => "teacher{$userId}@dup.local",
                'password' => Hash::make('12345678'), 'user_type' => 'Teacher', 'status' => 1,
            ],
        );
        DB::table('teachers')->updateOrInsert(
            ['id' => $userId],
            [
                'institute_id' => 1, 'branch_id' => 1, 'user_id' => $userId,
                'name' => 'Mohammad Ali', 'gender' => 'Male', 'phone' => '01700000'.$userId,
            ],
        );
    }

    $response = $this->get(route('teachers.index'));

    $response->assertOk();
    $response->assertSee('01700000201');
    $response->assertSee('01700000202');
});
