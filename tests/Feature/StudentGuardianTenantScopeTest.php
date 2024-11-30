<?php

use Illuminate\Support\Facades\DB;
use Modules\Authentication\Models\User;

/**
 * StudentController::update()'s guardian-sync step looked up an existing Parent
 * user by phone number ALONE. User is deliberately not scoped by a global tenant
 * scope, so a guardian phone number already registered under a different
 * institute matched here, and the following update() call rewrote that foreign
 * user's name and email -- a cross-tenant write reachable through an ordinary
 * student edit.
 */
it('does not update a same-phone guardian belonging to a different institute', function () {
    // A second institute holding the "victim" parent account.
    DB::table('institutes')->updateOrInsert(
        ['id' => 2],
        ['name' => 'Other Institute', 'domain' => 'other.local', 'platform' => 'WEB', 'status' => 1],
    );
    DB::table('branches')->updateOrInsert(
        ['id' => 2],
        ['institute_id' => 2, 'name' => 'Other Branch', 'status' => 1],
    );

    $victimParent = User::factory()->create([
        'institute_id' => 2, 'branch_id' => 2, 'user_type' => 'Parent',
        'name' => 'Victim Parent', 'phone' => '01911112222', 'status' => 1,
    ]);

    // Institute 1's admin editing one of their own students, entering the same
    // phone number as the guardian contact (coincidence, or an attacker probing).
    $admin = User::factory()->create([
        'institute_id' => 1, 'branch_id' => 1, 'user_type' => 'Admin', 'status' => 1,
    ]);
    $this->actingAs($admin);

    $studentUser = User::factory()->create([
        'institute_id' => 1, 'branch_id' => 1, 'user_type' => 'Student', 'status' => 1,
    ]);
    DB::table('students')->updateOrInsert(
        ['id' => 777],
        [
            'institute_id' => 1, 'branch_id' => 1, 'user_id' => $studentUser->id,
            'first_name' => 'Test', 'last_name' => 'Student', 'phone' => '01900000777',
        ],
    );
    DB::table('academic_years')->updateOrInsert(['id' => 1], ['session' => '2026', 'year' => '2026']);
    DB::table('settings')->updateOrInsert(
        ['institute_id' => 1, 'branch_id' => 1, 'name' => 'academic_year'],
        ['value' => '1'],
    );
    // Raw write bypasses Setting::saved() (see settings_map() in general.php).
    clear_settings_map();
    DB::table('classes')->updateOrInsert(['id' => 1], ['institute_id' => 1, 'branch_id' => 1, 'class_name' => 'One']);
    DB::table('sections')->updateOrInsert(['id' => 1], ['institute_id' => 1, 'branch_id' => 1, 'class_id' => 1, 'section_name' => 'A']);
    DB::table('student_sessions')->updateOrInsert(
        ['id' => 777],
        [
            'institute_id' => 1, 'branch_id' => 1, 'session_id' => 1, 'student_id' => 777,
            'class_id' => 1, 'section_id' => 1, 'roll' => '1',
        ],
    );

    $this->put(route('students.update', 777), [
        'first_name' => 'Test',
        'last_name' => 'Student',
        'email' => $studentUser->email,
        'phone' => '01900000777',
        'class_id' => 1,
        'section_id' => 1,
        'roll' => 1,
        'information_sent_to_name' => 'Attempted Rename',
        'information_sent_to_email' => 'attempted-rename@test.local',
        'information_sent_to_phone' => '01911112222', // the victim's phone
        'information_sent_to_relation' => 'Father',
        'information_sent_to_address' => 'Somewhere',
    ]);

    // The other institute's parent record must be untouched.
    $victimParent->refresh();
    expect($victimParent->name)->toBe('Victim Parent');
    expect($victimParent->institute_id)->toBe(2);

    // A new guardian user for THIS institute should exist instead, sharing the
    // phone number rather than hijacking the foreign account.
    $newGuardian = User::where('institute_id', 1)
        ->where('user_type', 'Parent')
        ->where('phone', '01911112222')
        ->first();
    expect($newGuardian)->not->toBeNull();
    expect($newGuardian->id)->not->toBe($victimParent->id);
});
