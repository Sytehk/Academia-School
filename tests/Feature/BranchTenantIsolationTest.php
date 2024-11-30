<?php

use Laravel\Passport\Passport;
use Modules\Authentication\Models\Branch;
use Modules\Authentication\Models\Institute;
use Modules\Authentication\Models\User;

/*
|--------------------------------------------------------------------------
| Regression: BranchController@update / @destroy resolved the branch with a
| bare Branch::find($id), so an institute admin could rename, re-parent or
| delete any other tenant's branch by guessing its id (IDOR).
|--------------------------------------------------------------------------
*/

function actingInstituteAdmin(): User
{
    $user = User::factory()->create(['user_type' => 'Admin', 'institute_id' => 1, 'branch_id' => 1]);
    Passport::actingAs($user, [], 'api');

    return $user;
}

test('an institute admin cannot update another institute\'s branch', function () {
    actingInstituteAdmin();

    $other = Institute::create(['name' => 'Other', 'domain' => 'other-branch.test', 'platform' => 'WEB', 'status' => 1]);
    $foreign = Branch::create(['institute_id' => $other->id, 'name' => 'Foreign Branch', 'status' => 1]);

    $this->putJson("/api/branches/{$foreign->id}", ['name' => 'Hijacked'])->assertStatus(404);

    expect($foreign->fresh()->name)->toBe('Foreign Branch');
    expect((int) $foreign->fresh()->institute_id)->toBe((int) $other->id);
});

test('an institute admin cannot delete another institute\'s branch', function () {
    actingInstituteAdmin();

    $other = Institute::create(['name' => 'Other2', 'domain' => 'other-branch-2.test', 'platform' => 'WEB', 'status' => 1]);
    $foreign = Branch::create(['institute_id' => $other->id, 'name' => 'Foreign Branch', 'status' => 1]);

    $this->deleteJson("/api/branches/{$foreign->id}")->assertStatus(404);

    expect(Branch::find($foreign->id))->not->toBeNull();
});

test('an institute admin can still update their own branch', function () {
    actingInstituteAdmin();

    $own = Branch::create(['institute_id' => 1, 'name' => 'Own Branch', 'status' => 1]);

    $this->putJson("/api/branches/{$own->id}", ['name' => 'Renamed'])->assertOk();

    expect($own->fresh()->name)->toBe('Renamed');
});
