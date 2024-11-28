<?php

use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Passport;
use Modules\Academic\Models\Student;
use Modules\Authentication\Models\User;
use Modules\ParentModule\Models\ParentModel;

/*
|--------------------------------------------------------------------------
| Phase 2 — Parent portal self-service profile.
|
| The admin APIParentController is now back-office only (RejectPortalApiUser),
| so parents get a dedicated self-service endpoint that can ONLY ever touch
| their own row — there is no id in the request.
|--------------------------------------------------------------------------
*/

function makeParentWithChild(): array
{
    $child = Student::factory()->create(['institute_id' => 1, 'branch_id' => 1]);
    $user = User::factory()->create([
        'user_type' => 'Parent', 'institute_id' => 1, 'branch_id' => 1, 'status' => 1,
        'password' => Hash::make('old-secret'),
    ]);
    $parent = ParentModel::create([
        'institute_id' => 1, 'branch_id' => 1, 'user_id' => $user->id,
        'student_id' => $child->id, 'parent_name' => 'Old Name',
        'parent_phone' => '01711110000', 'access_key' => 'old-secret',
    ]);
    $child->update(['parent_id' => $parent->id]);

    return [$user, $parent, $child];
}

test('a parent can update their own profile', function () {
    [$user, $parent] = makeParentWithChild();
    Passport::actingAs(User::findOrFail($user->id), [], 'api');

    $this->postJson('/api/parent/profile-update', [
        'name' => 'New Name',
        'phone' => '01722220000',
        'parent_profession' => 'Doctor',
    ])->assertOk();

    expect($parent->fresh()->parent_name)->toBe('New Name');
    expect($user->fresh()->name)->toBe('New Name');
    expect($parent->fresh()->parent_profession)->toBe('Doctor');
});

test('a parent cannot reach the back-office parent CRUD to edit another parent', function () {
    [$userA] = makeParentWithChild();
    [$userB, $parentB] = makeParentWithChild();

    Passport::actingAs(User::findOrFail($userA->id), [], 'api');

    // Admin route — blocked for portal users entirely.
    $this->putJson("/api/parents/{$parentB->id}", ['name' => 'hacked'])->assertStatus(403);

    expect($parentB->fresh()->parent_name)->toBe('Old Name');
});

test('a parent can change their password with the correct current password', function () {
    [$user] = makeParentWithChild();
    Passport::actingAs(User::findOrFail($user->id), [], 'api');

    $this->postJson('/api/parent/change-password', [
        'current_password' => 'wrong',
        'password' => 'brand-new-pass', 'password_confirmation' => 'brand-new-pass',
    ])->assertStatus(422);

    $this->postJson('/api/parent/change-password', [
        'current_password' => 'old-secret',
        'password' => 'brand-new-pass', 'password_confirmation' => 'brand-new-pass',
    ])->assertOk();

    expect(Hash::check('brand-new-pass', $user->fresh()->password))->toBeTrue();
});

test('deleting a parent detaches their children (no dangling parent_id)', function () {
    [$userA, $parentA, $childA] = makeParentWithChild();

    // Act as an admin for the back-office delete.
    $admin = User::factory()->create([
        'user_type' => 'Admin', 'institute_id' => 1, 'branch_id' => 1, 'status' => 1,
    ]);
    Passport::actingAs($admin, [], 'api');

    $this->deleteJson("/api/parents/{$parentA->id}")->assertOk();

    expect($childA->fresh()->parent_id)->toBeNull();
});
