<?php

use Illuminate\Support\Facades\DB;
use Modules\Authentication\Models\User;

/**
 * studentIdCardList() read class_id/section_id/group_id straight out of a
 * base64-decoded JSON payload with no check that they belong to the caller's
 * own institute/branch -- unlike every other controller handling these
 * fields, which calls assertClassSectionGroup() first. The student fetch
 * itself was still correctly tenant-scoped (never a cross-tenant data leak),
 * but an admin could hand-edit the decoded payload to enumerate the
 * class/section/group ids of classes outside their own branch.
 */
it('refuses to print cards for a class belonging to a different branch', function () {
    $admin = User::factory()->create([
        'institute_id' => 1, 'branch_id' => 1, 'user_type' => 'Admin', 'status' => 1,
    ]);
    $this->actingAs($admin);

    DB::table('branches')->updateOrInsert(
        ['id' => 2],
        ['institute_id' => 1, 'name' => 'Other Branch', 'status' => 1],
    );
    // A class in a DIFFERENT branch of the same institute.
    DB::table('classes')->updateOrInsert(
        ['id' => 999],
        ['institute_id' => 1, 'branch_id' => 2, 'class_name' => 'Foreign Class'],
    );

    $encoded = base64_encode(json_encode([
        'class_id' => 999, 'section_id' => 0, 'group_id' => 0, 'validity' => '2027-01-01',
    ]));

    $this->get(route('student.id.card.print', $encoded))->assertNotFound();
});

it('still prints for a class that does belong to the caller', function () {
    $admin = User::factory()->create([
        'institute_id' => 1, 'branch_id' => 1, 'user_type' => 'Admin', 'status' => 1,
    ]);
    $this->actingAs($admin);

    DB::table('classes')->updateOrInsert(
        ['id' => 1],
        ['institute_id' => 1, 'branch_id' => 1, 'class_name' => 'One'],
    );

    $encoded = base64_encode(json_encode([
        'class_id' => 1, 'section_id' => 0, 'group_id' => 0, 'validity' => '2027-01-01',
    ]));

    $this->get(route('student.id.card.print', $encoded))->assertOk();
});

it('404s instead of warning on a payload missing class_id entirely', function () {
    $admin = User::factory()->create([
        'institute_id' => 1, 'branch_id' => 1, 'user_type' => 'Admin', 'status' => 1,
    ]);
    $this->actingAs($admin);

    $encoded = base64_encode(json_encode(['validity' => '2027-01-01']));

    $this->get(route('student.id.card.print', $encoded))->assertNotFound();
});
